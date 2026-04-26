package com.cattlerfid.controller;

import com.cattlerfid.model.User;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidConstants;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNull;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.*;

class LoginControllerTest {

    private AuthenticationService authServiceMock;
    private SerialService serialServiceMock;
    private LoginController controller;
    private LoginController.LoginViewListener viewListenerMock;

    @BeforeEach
    void setUp() {
        authServiceMock = mock(AuthenticationService.class);
        serialServiceMock = mock(SerialService.class);
        viewListenerMock = mock(LoginController.LoginViewListener.class);

        controller = new LoginController(authServiceMock, serialServiceMock);
        controller.setViewListener(viewListenerMock);
    }

    @Test
    void testStartConnectionSuccess() {
        when(serialServiceMock.connect("COM3")).thenReturn(true);

        controller.startSerialConnection("COM3");

        verify(serialServiceMock).connect("COM3");
        verify(serialServiceMock).addMessageListener(any());
        verify(viewListenerMock).onSerialConnected();
    }

    @Test
    void testStartConnectionFailure() {
        when(serialServiceMock.connect("COM99")).thenReturn(false);

        controller.startSerialConnection("COM99");

        verify(viewListenerMock).onSerialError(anyString());
        verify(serialServiceMock, never()).addMessageListener(any());
    }

    @Test
    void testHandleMessageSuccessfulReadValidUser() {
        // Simula Tag chegando pela Serial
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_OK + ":V00000VET0001:FW:92";

        // Simula db mock
        User mockedVet = new User("joao_vet", "Joao");
        when(authServiceMock.authenticateByTag("V00000VET0001")).thenReturn(Optional.of(mockedVet));

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(authServiceMock).authenticateByTag("V00000VET0001");
        verify(viewListenerMock).onLoginSuccess(mockedVet);
        assertEquals(mockedVet, controller.getLoggedUser());
    }

    @Test
    void testHandleMessageSuccessfulReadInvalidUser() {
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_OK + ":V00000UNKNOWN1:FW:92";

        when(authServiceMock.authenticateByTag("V00000UNKNOWN1")).thenReturn(Optional.empty());

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(viewListenerMock).onLoginError(
                "Acesso Negado: Tag não cadastrada como funcionário VET.");
        assertNull(controller.getLoggedUser());
    }

    @Test
    void testInvalidTagPrefixLoginRejection() {
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_OK + ":UNKNOWN12345678:FW:92"; // Nao comeca com V

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(viewListenerMock)
                .onLoginError(
                        "Tag RFID inválida para Login (Veterinário). Lido: 'UNKNOWN12345678'");
        assertNull(controller.getLoggedUser());
        verify(authServiceMock, never()).authenticateByTag(any());
    }

    @Test
    void testHandleMessageArduinoErrorNoTag() {
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_NO_TAG + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(viewListenerMock).onLoginError("Nenhuma Tag ou Crachá detectado a tempo.");
        verify(authServiceMock, never()).authenticateByTag(any());
    }

    @Test
    void testHandleMessageArduinoErrorAuth() {
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_AUTH + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(viewListenerMock).onLoginError("Crachá com senha inválida ou não reconhecido.");
    }

    @Test
    void testHandleMessageArduinoErrorUnknown() {
        String simulatedArduinoResponse = "RES:" + RfidConstants.ID_LOGIN + ":" + RfidConstants.RES_ERR + ":HARDWARE_FAULT:FW:92";

        controller.handleIncomingSerialMessage(simulatedArduinoResponse);

        verify(viewListenerMock).onLoginError("Erro na leitura do chip: HARDWARE_FAULT");
    }

    @Test
    void testAttachToActiveSerialOpen() {
        when(serialServiceMock.isOpen()).thenReturn(true);

        controller.attachToActiveSerial();

        verify(serialServiceMock).addMessageListener(any());
        verify(viewListenerMock).onSerialConnected();
    }

    @Test
    void testAttachToActiveSerialClosed() {
        when(serialServiceMock.isOpen()).thenReturn(false);

        controller.attachToActiveSerial();

        verify(serialServiceMock, never()).addMessageListener(any());
        verify(viewListenerMock, never()).onSerialConnected();
    }

    @Test
    void testRequestCardLoginNotOpen() {
        when(serialServiceMock.isOpen()).thenReturn(false);

        controller.requestCardLogin();

        verify(viewListenerMock).onSerialError("Porta não conectada.");
        verify(serialServiceMock, never()).requestRead(anyString());
    }

    @Test
    void testRequestCardLoginSuccess() {
        when(serialServiceMock.isOpen()).thenReturn(true);

        controller.requestCardLogin();

        verify(viewListenerMock).onWaitingForCard();
        verify(serialServiceMock).requestRead(RfidConstants.ID_LOGIN);
    }

    @Test
    void testGetSerialService() {
        assertEquals(serialServiceMock, controller.getSerialService());
    }
}
