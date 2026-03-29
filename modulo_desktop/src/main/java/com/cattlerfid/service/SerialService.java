package com.cattlerfid.service;

import com.fazecast.jSerialComm.SerialPort;
import com.fazecast.jSerialComm.SerialPortDataListener;
import com.fazecast.jSerialComm.SerialPortEvent;

import java.io.OutputStream;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.function.Consumer;

public class SerialService {

    private final List<Consumer<String>> messageListeners = new ArrayList<>();
    private final StringBuilder messageBuffer = new StringBuilder(); 
    private final List<String> logHistory = new ArrayList<>();
    private final List<Consumer<String>> logListeners = new ArrayList<>();
    private SerialPort activePort;
    private OutputStream outputStream;
    private boolean simulationMode = false;

    // Usado pra testar localmente listando portas disponiveis
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

        // Garante que a mensagem injetada tenha o formato <CONTEUDO>
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
        logHistory.add(entry);
        for (Consumer<String> listener : logListeners) {
            listener.accept(entry);
        }
    }

    public List<String> getLogHistory() {
        return new ArrayList<>(logHistory);
    }

    public void addLogListener(Consumer<String> listener) {
        if (!logListeners.contains(listener)) {
            logListeners.add(listener);
        }
    }

    public void removeLogListener(Consumer<String> listener) {
        logListeners.remove(listener);
    }

    public boolean connect(String portName) {
        if (simulationMode) {
            appendLog("SYS", "Conexão Simulada iniciada.");
            return true;
        }

        activePort = SerialPort.getCommPort(portName);
        activePort.setComPortParameters(9600, 8, 1, 0); // 9600 baud rate, 8 bits de dados, 1 bit de parada, sem
        // paridade
        activePort.setComPortTimeouts(SerialPort.TIMEOUT_READ_SEMI_BLOCKING, 100, 0);

        if (activePort.openPort()) {
            outputStream = activePort.getOutputStream();
            setupListener();
            return true;
        }
        return false;
    }

    public void disconnect() {
        if (simulationMode) {
            appendLog("SYS", "Conexão Simulada encerrada.");
            return;
        }

        if (activePort != null && activePort.isOpen()) {
            activePort.removeDataListener();
            activePort.closePort();
        }
    }

    public boolean isOpen() {
        return simulationMode || (activePort != null && activePort.isOpen());
    }

    public void addMessageListener(Consumer<String> listener) {
        if (!messageListeners.contains(listener)) {
            messageListeners.add(listener);
        }
    }

    public void removeMessageListener(Consumer<String> listener) {
        messageListeners.remove(listener);
    }

    public void requestRead(String id) {
        sendCommand("<READ:" + id + ">\n");
    }

    public void requestWrite(String id, String data) {
        if (data.length() > 16) {
            data = data.substring(0, 16);
        }
        sendCommand("<WRITE:" + id + ":" + data + ">\n");
    }

    public void sendCommand(String command) {
        if (isOpen()) {
            appendLog("OUT", command.trim() + (simulationMode ? " (Simulado)" : ""));
            if (simulationMode) {
                return;
            }
            try {
                outputStream.write(command.getBytes());
                outputStream.flush();
            } catch (Exception e) {
                appendLog("ERROR", "Falha ao enviar: " + e.getMessage());
                e.printStackTrace();
            }
        } else {
            appendLog("ERROR", "Porta fechada. Tentou enviar: " + command.trim());
            System.err.println("Porta Serial não esta aberta para enviar comando: " + command);
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
                try {
                    byte[] newData = new byte[activePort.bytesAvailable()];
                    int numRead = activePort.readBytes(newData, newData.length);
                    for (int i = 0; i < numRead; i++) {
                        char c = (char) newData[i];

                        // Ignora quebra de linhas malucas no meio do payload enviadas pelo println do
                        // Arduino
                        if (c == '\r' || c == '\n') {
                            continue;
                        }

                        messageBuffer.append(c);

                        // O Arduino envia < no começo e > no fim. Vamos ler ate fechar o >.
                        if (c == '>') {
                            String message = messageBuffer.toString().trim();
                            messageBuffer.setLength(0); // Limpa o buffer para a proxima

                            if (!message.isEmpty()) {
                                appendLog("IN", message);

                                // Valida e extrai o conteúdo entre < e >
                                int startPacketIdx = message.indexOf('<');
                                if (startPacketIdx != -1 && message.endsWith(">")) {
                                    // Remove sujeira antes do < e remove os <>
                                    String cleanMessage = message.substring(startPacketIdx + 1, message.length() - 1);
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
                    e.printStackTrace();
                }
            }
        });
    }
}
