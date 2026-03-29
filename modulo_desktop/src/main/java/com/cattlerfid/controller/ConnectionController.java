package com.cattlerfid.controller;

import com.cattlerfid.util.RfidConstants;
import com.cattlerfid.service.SerialService;

import java.util.function.Consumer;

public class ConnectionController {

    private final SerialService serialService;
    private ConnectionViewListener viewListener;
    private boolean testingConnection = false;
    private final Consumer<String> serialListener = this::handleIncomingSerialMessage;

    public ConnectionController(SerialService serialService) {
        this.serialService = serialService;
    }

    public void setViewListener(ConnectionViewListener listener) {
        this.viewListener = listener;
    }

    public void startSerialConnection(String portName) {
        if (serialService.connect(portName)) {
            serialService.addMessageListener(serialListener);
            if (viewListener != null)
                viewListener.onSerialConnected();
        } else {
            viewListener.onSerialError("Não foi possível conectar na porta " + portName);
        }
    }

    public void disconnectSerial() {
        serialService.disconnect();
        if (viewListener != null) {
            viewListener.onSerialDisconnected();
        }
    }

    public void detachSerial() {
        serialService.removeMessageListener(serialListener);
    }

    public void requestTestRead() {
        if (!serialService.isOpen()) {
            if (viewListener != null)
                viewListener.onSerialError("Porta não conectada.");
            return;
        }
        testingConnection = true;
        if (viewListener != null)
            viewListener.onWaitingForTestTag();
        serialService.requestRead(RfidConstants.ID_CONN);
    }

    private void handleIncomingSerialMessage(String message) {
        if (!testingConnection)
            return;

        String[] parts = message.split(":");
        if (parts.length >= 3) {
            // Valida se o pacote é para este controlador
            if (!parts[1].equals(RfidConstants.ID_CONN)) {
                return;
            }

            if (parts[2].equals(RfidConstants.RES_OK)) {
                String tagContent = parts[3].trim();
                testingConnection = false;
                if (viewListener != null) {
                    viewListener.onTestTagReadSuccess(tagContent);
                }
            } else if (parts[2].equals(RfidConstants.RES_ERR)) {
                if (viewListener != null) {
                    if (parts[3].equals(RfidConstants.ERR_NO_TAG))
                        viewListener.onSerialError("Nenhuma Tag detectada a tempo. Tente novamente.");
                    else
                        viewListener.onSerialError("Erro na leitura da tag de teste: " + parts[2]);
                }
            }
        }
    }

    public SerialService getSerialService() {
        return serialService;
    }

    public void setSimulationMode(boolean active) {
        serialService.setSimulationMode(active);
    }

    public interface ConnectionViewListener {
        void onSerialConnected();

        void onSerialDisconnected();

        void onSerialError(String message);

        void onWaitingForTestTag();

        void onTestTagReadSuccess(String tagContent);
    }
}
