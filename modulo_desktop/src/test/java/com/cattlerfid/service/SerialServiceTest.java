package com.cattlerfid.service;

import org.junit.jupiter.api.Test;

import java.util.List;
import java.util.concurrent.atomic.AtomicReference;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNotSame;

class SerialServiceTest {

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
        String hugePayload = "BoiBandido123456789";
        mockService.requestWrite("TEST", hugePayload);
        assertEquals("<WRITE:TEST:BoiBandido123456>\n", mockService.getLastSentCommand());
    }

    @Test
    void testIncomingMessageParsing() {
        MockSerialService mockService = new MockSerialService();
        AtomicReference<String> receivedParsedMessage = new AtomicReference<>("");

        mockService.simulateArduinoIncomingLine("<RES:TEST:OK:João :FW:92>", receivedParsedMessage::set);

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

        assertEquals("TAG:123", received.get(), "Listener should receive the injected message without brackets");
    }

    @Test
    void testSimulationMode_SendCommandDoesNotError() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        service.connect("COM1");

        service.sendCommand("<READ>");
        assertEquals(true, service.isOpen());
    }

    @Test
    void testGetLogHistory_returnsDefensiveCopy() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        service.connect("SIM");

        List<String> history1 = service.getLogHistory();
        List<String> history2 = service.getLogHistory();

        assertNotSame(history1, history2, "getLogHistory should return a new list each time");
        history1.clear();
        assertEquals(history2.size(), service.getLogHistory().size(), "Modifying returned list must not affect service state");
    }

    @Test
    void testLogListener_firesOnSimulatedMessage() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        AtomicReference<String> lastLog = new AtomicReference<>("");

        service.addLogListener(lastLog::set);
        service.injectMessage("SOME:MSG");

        assertEquals(true, lastLog.get().contains("SOME:MSG"), "Log listener should receive entries when messages arrive");
    }

    @Test
    void testRemoveLogListener_stopsReceivingEntries() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        AtomicReference<Integer> callCount = new AtomicReference<>(0);

        Runnable[] holder = new Runnable[1];
        java.util.function.Consumer<String> listener = s -> callCount.updateAndGet(c -> c + 1);
        service.addLogListener(listener);
        service.injectMessage("MSG1");
        service.removeLogListener(listener);
        service.injectMessage("MSG2");

        assertEquals(1, callCount.get(), "Listener should not fire after removal");
    }

    @Test
    void testAddMessageListener_deduplicates() {
        SerialService service = new SerialService();
        service.setSimulationMode(true);
        AtomicReference<Integer> callCount = new AtomicReference<>(0);

        java.util.function.Consumer<String> listener = s -> callCount.updateAndGet(c -> c + 1);
        service.addMessageListener(listener);
        service.addMessageListener(listener);
        service.injectMessage("DEDUP:TEST");

        assertEquals(1, callCount.get(), "Duplicate listeners should not fire twice");
    }

    class MockSerialService extends SerialService {

        private String lastSentCommand = "";

        @Override
        public boolean connect(String portName) {
            return true;
        }

        @Override
        public boolean isOpen() {
            return true;
        }

        @Override
        public void sendCommand(String command) {
            this.lastSentCommand = command;
        }

        public String getLastSentCommand() {
            return lastSentCommand;
        }

        public void simulateArduinoIncomingLine(String line, java.util.function.Consumer<String> callback) {
            if (line.startsWith("<") && line.endsWith(">")) {
                callback.accept(line.substring(1, line.length() - 1));
            }
        }
    }
}
