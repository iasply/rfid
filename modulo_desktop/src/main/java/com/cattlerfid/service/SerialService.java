package com.cattlerfid.service;

import com.fazecast.jSerialComm.SerialPort;
import com.fazecast.jSerialComm.SerialPortDataListener;
import com.fazecast.jSerialComm.SerialPortEvent;

import java.io.OutputStream;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.CopyOnWriteArrayList;
import java.util.function.Consumer;

public class SerialService {

    private final List<Consumer<String>> messageListeners = new CopyOnWriteArrayList<>();
    private final List<Consumer<String>> logListeners = new CopyOnWriteArrayList<>();
    private final List<String> logHistory = new ArrayList<>();

    private final StringBuilder messageBuffer = new StringBuilder();
    private final Object portLock = new Object();
    private volatile SerialPort activePort;
    private volatile OutputStream outputStream;
    private volatile boolean simulationMode = false;

    public static String[] getAvailablePorts() {
        SerialPort[] ports = SerialPort.getCommPorts();
        String[] portNames = new String[ports.length];
        for (int i = 0; i < ports.length; i++) {
            portNames[i] = ports[i].getSystemPortName();
        }
        return portNames;
    }

    public boolean isSimulationMode() {
        return simulationMode;
    }

    public void setSimulationMode(boolean simulationMode) {
        this.simulationMode = simulationMode;
        appendLog("SYS", "Modo Simulação " + (simulationMode ? "Ativado" : "Desativado"));
    }

    public void injectMessage(String message) {
        if (!simulationMode)
            return;

        String formattedMessage = message;
        if (!formattedMessage.startsWith("<"))
            formattedMessage = "<" + formattedMessage;
        if (!formattedMessage.endsWith(">"))
            formattedMessage = formattedMessage + ">";

        appendLog("IN", formattedMessage + " (Simulado)");

        String payload = formattedMessage.substring(1, formattedMessage.length() - 1);
        for (Consumer<String> listener : messageListeners) {
            listener.accept(payload);
        }
    }

    private void appendLog(String origin, String message) {
        String time = LocalTime.now().format(DateTimeFormatter.ofPattern("HH:mm:ss.SSS"));
        String entry = String.format("[%s] %-5s %s", time, origin, message);
        synchronized (logHistory) {
            logHistory.add(entry);
        }
        for (Consumer<String> listener : logListeners) {
            listener.accept(entry);
        }
    }

    public List<String> getLogHistory() {
        synchronized (logHistory) {
            return new ArrayList<>(logHistory);
        }
    }

    public void addLogListener(Consumer<String> listener) {
        if (!logListeners.contains(listener))
            logListeners.add(listener);
    }

    public void removeLogListener(Consumer<String> listener) {
        logListeners.remove(listener);
    }

    public boolean connect(String portName) {
        if (simulationMode) {
            appendLog("SYS", "Conexão Simulada iniciada.");
            return true;
        }

        synchronized (portLock) {
            activePort = SerialPort.getCommPort(portName);
            activePort.setComPortParameters(9600, 8, 1, 0);
            activePort.setComPortTimeouts(SerialPort.TIMEOUT_READ_SEMI_BLOCKING, 100, 0);

            if (activePort.openPort()) {
                outputStream = activePort.getOutputStream();
                setupListener();
                return true;
            }
            return false;
        }
    }

    public void disconnect() {
        if (simulationMode) {
            appendLog("SYS", "Conexão Simulada encerrada.");
            return;
        }

        synchronized (portLock) {
            if (activePort != null && activePort.isOpen()) {
                activePort.removeDataListener();
                outputStream = null;
                activePort.closePort();
            }
        }
    }

    public boolean isOpen() {
        if (simulationMode)
            return true;
        SerialPort port = activePort;
        return port != null && port.isOpen();
    }

    public void addMessageListener(Consumer<String> listener) {
        if (!messageListeners.contains(listener))
            messageListeners.add(listener);
    }

    public void removeMessageListener(Consumer<String> listener) {
        messageListeners.remove(listener);
    }

    public void requestRead(String id) {
        sendCommand("<READ:" + id + ">\n");
    }

    public void requestWrite(String id, String data) {
        if (data.length() > 16)
            data = data.substring(0, 16);
        sendCommand("<WRITE:" + id + ":" + data + ">\n");
    }

    public void sendCommand(String command) {
        if (!isOpen()) {
            appendLog("ERROR", "Porta fechada. Tentou enviar: " + command.trim());
            return;
        }
        appendLog("OUT", command.trim() + (simulationMode ? " (Simulado)" : ""));
        if (simulationMode)
            return;

        OutputStream out = outputStream;
        if (out == null)
            return;
        try {
            out.write(command.getBytes());
            out.flush();
        } catch (Exception e) {
            appendLog("ERROR", "Falha ao enviar: " + e.getMessage());
        }
    }

    private void setupListener() {
        activePort.addDataListener(new SerialPortDataListener() {

            @Override
            public int getListeningEvents() {
                return SerialPort.LISTENING_EVENT_DATA_AVAILABLE;
            }

            @Override
            public void serialEvent(SerialPortEvent event) {
                if (event.getEventType() != SerialPort.LISTENING_EVENT_DATA_AVAILABLE)
                    return;
                SerialPort port = activePort;
                if (port == null)
                    return;
                try {
                    int available = port.bytesAvailable();
                    if (available <= 0)
                        return;
                    byte[] newData = new byte[available];
                    int numRead = port.readBytes(newData, newData.length);
                    for (int i = 0; i < numRead; i++) {
                        char c = (char) newData[i];
                        if (c == '\r' || c == '\n')
                            continue;

                        messageBuffer.append(c);

                        if (c == '>') {
                            String message = messageBuffer.toString().trim();
                            messageBuffer.setLength(0);

                            if (!message.isEmpty()) {
                                appendLog("IN", message);
                                int startIdx = message.indexOf('<');
                                if (startIdx != -1 && message.endsWith(">")) {
                                    String cleanMessage = message.substring(startIdx + 1, message.length() - 1);
                                    for (Consumer<String> listener : messageListeners) {
                                        listener.accept(cleanMessage);
                                    }
                                } else {
                                    appendLog("WARN", "Pacote incompleto ou inválido ignorado: " + message);
                                }
                            }
                        }
                    }
                } catch (Exception e) {
                    appendLog("ERROR", "Erro na leitura serial: " + e.getMessage());
                }
            }
        });
    }
}
