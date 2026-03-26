package com.cattlerfid.controller;

import com.cattlerfid.model.User;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidGenerator;

import java.util.Optional;
import java.util.function.Consumer;

public class LoginController {

    private final AuthenticationService authService;
    private final SerialService serialService;

    private LoginViewListener viewListener;
    private User loggedUser;
    private final Consumer<String> serialListener = this::handleIncomingSerialMessage;

    public LoginController(AuthenticationService authService, SerialService serialService) {
        this.authService = authService;
        this.serialService = serialService;
    }

    public void setViewListener(LoginViewListener listener) {
        this.viewListener = listener;
    }

    public void startSerialConnection(String portName) {
        if (serialService.connect(portName)) {
            serialService.addMessageListener(serialListener);
            if (viewListener != null)
                viewListener.onSerialConnected();
        } else {
            if (viewListener != null)
                viewListener.onSerialError("Não foi possível conectar na porta " + portName);
        }
    }

    public void attachToActiveSerial() {
        if (serialService.isOpen()) {
            serialService.addMessageListener(serialListener);
            if (viewListener != null)
                viewListener.onSerialConnected();
        }
    }

    public void detachSerial() {
        serialService.removeMessageListener(serialListener);
    }

    public void requestCardLogin() {
        if (!serialService.isOpen()) {
            if (viewListener != null)
                viewListener.onSerialError("Porta não conectada.");
            return;
        }
        if (viewListener != null)
            viewListener.onWaitingForCard();
        serialService.requestRead();
    }

    protected void handleIncomingSerialMessage(String message) {
        String[] parts = message.split(":");

        if (parts.length >= 2) {
            if (parts[1].equals("OK")) {
                String tagContent = parts[2].trim();
                if (!RfidGenerator.isVetTag(tagContent)) {
                    if (viewListener != null) {
                        viewListener.onLoginError(
                                "Tag RFID inválida para Login (Veterinário). Lido: '" + tagContent + "'");
                    }
                    return;
                }
                attemptLogin(tagContent);
            } else if (parts[1].equals("ERR")) {
                if (viewListener != null) {
                    if (parts[2].equals("NO_TAG"))
                        viewListener.onLoginError("Nenhuma Tag ou Crachá detectado a tempo.");
                    else if (parts[2].equals("AUTH"))
                        viewListener.onLoginError("Crachá com senha inválida ou não reconhecido.");
                    else
                        viewListener.onLoginError("Erro na leitura do chip: " + parts[2]);
                }
            }
        }
    }

    private void attemptLogin(String tag) {
        Optional<User> user = authService.authenticateByTag(tag);
        if (user.isPresent()) {
            this.loggedUser = user.get();
            if (viewListener != null)
                viewListener.onLoginSuccess(this.loggedUser);
        } else {
            if (viewListener != null)
                viewListener.onLoginError("Acesso Negado: Tag não cadastrada como funcionário VET.");
        }
    }

    public User getLoggedUser() {
        return loggedUser;
    }

    public SerialService getSerialService() {
        return serialService;
    }

    public interface LoginViewListener {
        void onLoginSuccess(User user);

        void onLoginError(String message);

        void onSerialConnected();

        void onSerialError(String message);

        void onWaitingForCard();
    }
}
