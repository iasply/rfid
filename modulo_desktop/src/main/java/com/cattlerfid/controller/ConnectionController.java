package com.cattlerfid.controller;

import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidConstants;

import java.util.function.Consumer;

public class ConnectionController {

    private final SerialService serialService;

    private ConnectionViewListener viewListener = new ConnectionViewListener() {

        public void onSerialConnected() {}

        public void onSerialDisconnected() {}

        public void onSerialError(String message) {}

        public void onWaitingForTestTag() {}

        public void onTestTagReadSuccess(String tagContent) {}
    };
    private State state = State.IDLE;
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
            viewListener.onSerialConnected();
        } else {
            viewListener.onSerialError("Não foi possível conectar na porta " + portName);
        }
    }

    public void disconnectSerial() {
        serialService.disconnect();
        viewListener.onSerialDisconnected();
    }

    public void detachSerial() {
        serialService.removeMessageListener(serialListener);
    }

    public void requestTestRead() {
        if (!serialService.isOpen()) {
            viewListener.onSerialError("Porta não conectada.");
            return;
        }
        state = State.TESTING;
        viewListener.onWaitingForTestTag();
        serialService.requestRead(RfidConstants.ID_CONN);
    }

    private void handleIncomingSerialMessage(String message) {
        if (state != State.TESTING)
            return;

        String[] parts = message.split(":");
        if (parts.length >= 3) {
            if (!parts[1].equals(RfidConstants.ID_CONN)) {
                return;
            }

            if (parts[2].equals(RfidConstants.RES_OK)) {
                String tagContent = parts[3].trim();
                state = State.IDLE;
                viewListener.onTestTagReadSuccess(tagContent);
            } else if (parts[2].equals(RfidConstants.RES_ERR)) {
                if (parts[3].equals(RfidConstants.ERR_NO_TAG))
                    viewListener.onSerialError("Nenhuma Tag detectada a tempo. Tente novamente.");
                else
                    viewListener.onSerialError("Erro na leitura da tag de teste: " + parts[3]);
            }
        }
    }

    public SerialService getSerialService() {
        return serialService;
    }

    public void setSimulationMode(boolean active) {
        serialService.setSimulationMode(active);
    }

    private enum State {
        IDLE,
        TESTING
    }

    public interface ConnectionViewListener {

        void onSerialConnected();

        void onSerialDisconnected();

        void onSerialError(String message);

        void onWaitingForTestTag();

        void onTestTagReadSuccess(String tagContent);
    }
}
