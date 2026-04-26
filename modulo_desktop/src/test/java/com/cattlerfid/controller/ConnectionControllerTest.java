package com.cattlerfid.controller;

import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidConstants;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.mockito.ArgumentCaptor;

import java.util.function.Consumer;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.*;

class ConnectionControllerTest {

    private SerialService serialServiceMock;
    private ConnectionController controller;
    private ConnectionController.ConnectionViewListener viewListenerMock;

    @BeforeEach
    void setUp() {
        serialServiceMock = mock(SerialService.class);
        viewListenerMock = mock(ConnectionController.ConnectionViewListener.class);

        controller = new ConnectionController(serialServiceMock);
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

        verify(serialServiceMock).connect("COM99");
        verify(viewListenerMock).onSerialError(anyString());
        verify(serialServiceMock, never()).addMessageListener(any());
    }

    @Test
    void testRequestTestReadNotOpen() {
        when(serialServiceMock.isOpen()).thenReturn(false);

        controller.requestTestRead();

        verify(viewListenerMock).onSerialError("Porta não conectada.");
        verify(serialServiceMock, never()).requestRead(anyString());
    }

    @Test
    void testRequestTestReadSuccess() {
        when(serialServiceMock.isOpen()).thenReturn(true);

        controller.requestTestRead();

        verify(viewListenerMock).onWaitingForTestTag();
        verify(serialServiceMock).requestRead(RfidConstants.ID_CONN);
    }

    @Test
    @SuppressWarnings("unchecked")
    void testHandleMessageSuccessfulTestRead() {
        // Mock a conexão
        when(serialServiceMock.connect("COM1")).thenReturn(true);

        // Captura o lambda setado no onMessageReceived
        ArgumentCaptor<Consumer<String>> captor = ArgumentCaptor.forClass(Consumer.class);

        // Dispara conexão e salva o listener interno na variavel
        controller.startSerialConnection("COM1");
        verify(serialServiceMock).addMessageListener(captor.capture());
        Consumer<String> messageHandler = captor.getValue();

        // 1. Enviamos MSG mas a boolean testingConnection está False por padrao
        messageHandler.accept(
                "RES:" + RfidConstants.ID_CONN + ":" + RfidConstants.RES_OK + ":QUALQUER_TAG :FW:92");
        verify(viewListenerMock, never()).onTestTagReadSuccess(anyString());

        // 2. Simulamos o clique no botão Testar
        when(serialServiceMock.isOpen()).thenReturn(true);
        controller.requestTestRead();

        // 3. Enviamos a mensagem de novo, agora a boolean esta ativada
        String tagTestMessage = "RES:" + RfidConstants.ID_CONN + ":" + RfidConstants.RES_OK + ":QUALQUER_TAG_16   :FW:92";
        messageHandler.accept(tagTestMessage);

        verify(viewListenerMock).onTestTagReadSuccess("QUALQUER_TAG_16");
    }

    @Test
    @SuppressWarnings("unchecked")
    void testHandleMessageErrorNoTag() {
        when(serialServiceMock.connect("COM1")).thenReturn(true);
        ArgumentCaptor<Consumer<String>> captor = ArgumentCaptor.forClass(Consumer.class);

        controller.startSerialConnection("COM1");
        verify(serialServiceMock).addMessageListener(captor.capture());
        Consumer<String> messageHandler = captor.getValue();

        // Ativa o teste
        when(serialServiceMock.isOpen()).thenReturn(true);
        controller.requestTestRead();

        // Dispara mensagem de erro vindo do Arduino
        messageHandler.accept(
                "RES:" + RfidConstants.ID_CONN + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_NO_TAG + ":FW:00");
        verify(viewListenerMock).onSerialError("Nenhuma Tag detectada a tempo. Tente novamente.");
    }

    @Test
    void testGetSerialService() {
        assertEquals(serialServiceMock, controller.getSerialService());
    }

    @Test
    void testDisconnectSerial() {
        controller.disconnectSerial();

        verify(serialServiceMock).disconnect();
        verify(viewListenerMock).onSerialDisconnected();
    }
}
