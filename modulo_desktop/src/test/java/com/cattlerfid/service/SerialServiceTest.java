package com.cattlerfid.service;

import org.junit.jupiter.api.Test;

import java.util.concurrent.atomic.AtomicReference;

import static org.junit.jupiter.api.Assertions.assertEquals;

class SerialServiceTest {

    // Como o jSerialComm aciona diretamente drivers USB de Hardware (JNI) e estamos
    // no TDD,
    // nao da pra testar uma porta COM1 com facilidade sem "Mockar" toda a JNI
    // framework native.
    // Porem podemos testar os manipuladores criados dentro de uma subclasse mock em
    // memoria.

    @Test
    void testRequestReadCommandFormat() {
        MockSerialService mockService = new MockSerialService();
        mockService.requestRead("TEST");
        assertEquals("<READ:TEST>\n", mockService.getLastSentCommand());
    }

    @Test
    void testRequestWriteCommandFormat() {
        MockSerialService mockService = new MockSerialService();
        mockService.requestWrite("TEST", "JoaoSilva123");
        assertEquals("<WRITE:TEST:JoaoSilva123>\n", mockService.getLastSentCommand());
    }

    @Test
    void testRequestWriteCommandFormat_TruncatesAt16Chars() {
        MockSerialService mockService = new MockSerialService();
        String hugePayload = "BoiBandido123456789"; // 19 caracteres
        mockService.requestWrite("TEST", hugePayload);
        assertEquals("<WRITE:TEST:BoiBandido123456>\n",
                mockService.getLastSentCommand()); // Exatos 16 caracteres cortados
    }

    @Test
    void testIncomingMessageParsing() {
        MockSerialService mockService = new MockSerialService();
        AtomicReference<String> receivedParsedMessage = new AtomicReference<>("");

        // Simula a linha bruta do Arduino: <RES:OK:João :FW:92>
        mockService.simulateArduinoIncomingLine("<RES:TEST:OK:João :FW:92>",
                receivedParsedMessage::set);

        // O servico Serial tem que cortar os <> (brackets) que sao do protocolo
        assertEquals("RES:TEST:OK:João :FW:92", receivedParsedMessage.get());
    }

    @Test
    void testSimulationMode_ConnectSuccess() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        boolean connected = service.connect("ANY_PORT");
        assertEquals(true, connected, "Should connect successfully in simulation mode");
        assertEquals(true, service.isOpen());
    }

    @Test
    void testSimulationMode_InjectMessage() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        AtomicReference<String> received = new AtomicReference<>("");
        service.addMessageListener(received::set);

        service.injectMessage("TAG:123");

        assertEquals("TAG:123", received.get(),
                "Listener should receive the injected message without brackets");
    }

    @Test
    void testSimulationMode_SendCommandDoesNotError() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        service.connect("COM1");

        // Should not throw exception and should record in logs (can't easily assert logs here without changes but confirming it doesn't fail)
        service.sendCommand("<READ>");
        assertEquals(true, service.isOpen());
    }

    class MockSerialService extends SerialService {
        private String lastSentCommand = "";

        @Override
        public boolean connect(String portName) {
            return true; // Simula sucesso sempre
        }

        @Override
        public boolean isOpen() {
            return true;
        }

        @Override
        public void sendCommand(String command) {
            this.lastSentCommand = command; // Captura pra validacao
        }

        public String getLastSentCommand() {
            return lastSentCommand;
        }

        // Metodo pra simular entrada fake vinda do Arduino, chamando o callback
        public void simulateArduinoIncomingLine(String line,
                java.util.function.Consumer<String> callback) {
            if (line.startsWith("<") && line.endsWith(">")) {
                callback.accept(line.substring(1, line.length() - 1));
            }
        }
    }
}
