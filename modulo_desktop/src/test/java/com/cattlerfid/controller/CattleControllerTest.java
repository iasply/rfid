package com.cattlerfid.controller;

import com.cattlerfid.model.Cattle;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidConstants;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.Mockito.*;

class CattleControllerTest {

    private CattleApiService apiServiceMock;
    private SerialService serialServiceMock;
    private CattleController controller;
    private CattleController.CattleViewListener viewListenerMock;

    @BeforeEach
    void setUp() {
        apiServiceMock = mock(CattleApiService.class);
        serialServiceMock = mock(SerialService.class);
        viewListenerMock = mock(CattleController.CattleViewListener.class);

        controller = new CattleController(apiServiceMock, serialServiceMock);
        when(serialServiceMock.isOpen()).thenReturn(true);
        controller.setViewListener(viewListenerMock);
    }

    @Test
    void testRequestReadTagNotConnected() {
        when(serialServiceMock.isOpen()).thenReturn(false);
        controller.requestReadTag();

        verify(viewListenerMock).onRfidReadError(anyString());
        verify(serialServiceMock, never()).requestRead(anyString());
    }

    @Test
    void testRequestReadTagConnected() {
        controller.requestReadTag();
        verify(serialServiceMock).requestRead(RfidConstants.ID_CATTLE);
    }

    @Test
    void testRequestWriteTagNotConnected() {
        when(serialServiceMock.isOpen()).thenReturn(false);
        controller.requestWriteTag("C1234567890");

        verify(viewListenerMock).onRfidWriteError(anyString());
        verify(serialServiceMock, never()).requestWrite(anyString(), anyString());
    }

    @Test
    void testRequestWriteTagConnected() {
        controller.requestWriteTag("C1234567890");
        verify(serialServiceMock).requestRead(RfidConstants.ID_CATTLE); // Valida fisicamente primeiro
    }

    @Test
    void testHandleMessageWriteReadUserTagBlocked() {
        controller.requestWriteTag("C123456");
        controller.handleIncomingSerialMessage("RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":VADMIN01:FW:92");

        verify(viewListenerMock).onRfidWriteError(contains("Bloqueado"));
        verify(serialServiceMock, never()).requestWrite(anyString(), anyString());
    }

    @Test
    void testHandleMessageWriteReadNewTagAllowed() {
        controller.requestWriteTag("C123456");
        controller.handleIncomingSerialMessage("RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":COLDTAG123:FW:92");

        verify(serialServiceMock).requestWrite(RfidConstants.ID_CATTLE, "C123456");
    }

    /**
     * Regression test for the reported missing test.
     */
    @Test
    void testHandleMessageWriteReadNoTagError() {
        controller.requestWriteTag("C123456");
        controller.handleIncomingSerialMessage("RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_NO_TAG + ":FW:92");

        verify(viewListenerMock).onRfidWriteError(contains("Nenhuma Tag detectada"));
        verify(serialServiceMock, never()).requestWrite(anyString(), anyString());
    }

    @Test
    void testHandleMessageReadSuccessExistingCattle() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":CVACA00000000001:FW:92";
        Cattle existingCattle = new Cattle("CVACA00000000001", "Mimosa", 400, "2023-01-01");
        existingCattle.setId(10);

        when(apiServiceMock.getCattleByTag("CVACA00000000001")).thenReturn(Optional.of(existingCattle));

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(apiServiceMock).getCattleByTag("CVACA00000000001");
        verify(viewListenerMock).onRfidReadSuccess(existingCattle);
    }

    @Test
    void testHandleMessageReadSuccessNewCattle() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":CDESCONHECIDO12:FW:92";

        when(apiServiceMock.getCattleByTag("CDESCONHECIDO12")).thenReturn(Optional.empty());

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(apiServiceMock).getCattleByTag("CDESCONHECIDO12");
        verify(viewListenerMock).onRfidReadError("Animal não encontrado na base de dados. Por favor, cadastre-o primeiro.");

        Cattle c = controller.getCurrentEditingCattle();
        assertNull(c);
    }

    @Test
    void testHandleMessageReadUserTagWarning() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":VVET000000000001:FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidReadError(contains("Tag de Usuário"));
    }

    @Test
    void testHandleMessageWriteSuccess() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":" + RfidConstants.MSG_WROTE + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidWriteSuccess();
    }

    @Test
    void testHandleMessageWriteError() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_WRITE_FAILED + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidWriteError("Erro no barramento SPI ao gravar dados na Tag.");
    }

    @Test
    void testSaveCattleData_shouldCallSaveCattle_whenIdIsZero() {
        Cattle newCattle = new Cattle("C002", "Newbie", 100.0, "2024-03-08");
        newCattle.setId(0);

        when(apiServiceMock.saveCattle(newCattle)).thenReturn(true);

        controller.saveCattleData(newCattle);

        verify(apiServiceMock).saveCattle(newCattle);
        verify(viewListenerMock).onApiSaveSuccess();
    }

    @Test
    void testSaveCattleData_shouldCallUpdateCattle_whenIdIsPresent() {
        Cattle existingCattle = new Cattle("C001", "Mimosa", 400.0, "2024-01-01");
        existingCattle.setId(10);

        when(apiServiceMock.updateCattle(existingCattle)).thenReturn(true);

        controller.saveCattleData(existingCattle);

        verify(apiServiceMock).updateCattle(existingCattle);
        verify(apiServiceMock, never()).saveCattle(any(Cattle.class));
        verify(viewListenerMock).onApiSaveSuccess();
    }

    /**
     * Regression test for the 422 error fix. saveVaccineData should NOT call saveCattle/updateCattle because the server handles it.
     */
    @Test
    void testSaveVaccineData_shouldOnlyCallSaveVaccine() {
        com.cattlerfid.model.Vaccine vaccine = new com.cattlerfid.model.Vaccine();
        Cattle cattle = new Cattle("C123", "Boi", 100.0, "2023-01-01");
        cattle.setId(10);

        when(apiServiceMock.saveVaccine(vaccine)).thenReturn(true);

        controller.saveVaccineData(vaccine, cattle, 150.0);

        verify(apiServiceMock).saveVaccine(vaccine);
        verify(apiServiceMock, never()).saveCattle(any(Cattle.class));
        verify(apiServiceMock, never()).updateCattle(any(Cattle.class));
        verify(viewListenerMock).onApiSaveSuccess();
    }

    @Test
    void testSaveVaccineDataError() {
        com.cattlerfid.model.Vaccine vaccine = new com.cattlerfid.model.Vaccine();
        Cattle cattle = new Cattle("C123", "Boi", 100.0, "2023-01-01");

        when(apiServiceMock.saveVaccine(vaccine)).thenReturn(false);

        controller.saveVaccineData(vaccine, cattle, 150.0);

        verify(viewListenerMock).onApiSaveError("Falha ao registrar a vacina no banco de dados.");
    }

    @Test
    void testHandleMessageReadInvalidTagFormat() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_OK + ":XINVALID12345678:FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidReadError(contains("Formato de Tag animal inválido ou inválida para o sistema"));
    }

    @Test
    void testHandleMessageReadErrorNoTag() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_NO_TAG + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidReadError("Nenhuma Tag detectada.");
    }

    @Test
    void testHandleMessageReadErrorAuth() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_AUTH + ":FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidReadError("Erro de autenticação da Tag.");
    }

    @Test
    void testHandleMessageReadErrorUnknown() {
        String simulatedSerialMsg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":UNKNOWN_FAULT:FW:92";

        controller.handleIncomingSerialMessage(simulatedSerialMsg);

        verify(viewListenerMock).onRfidReadError("Erro desconhecido: UNKNOWN_FAULT");
    }

    @Test
    void testGetters() {
        assertEquals(serialServiceMock, controller.getSerialService());
        assertEquals(apiServiceMock, controller.getApiService());
    }

    @Test
    void testNoListenerSet_readTagDoesNotThrowNPE() {
        CattleController noListenerController = new CattleController(apiServiceMock, serialServiceMock);
        when(serialServiceMock.isOpen()).thenReturn(false);

        assertDoesNotThrow(noListenerController::requestReadTag, "Controller without viewListener must not throw NPE on requestReadTag");
    }

    @Test
    void testNoListenerSet_handleMessageDoesNotThrowNPE() {
        CattleController noListenerController = new CattleController(apiServiceMock, serialServiceMock);
        String msg = "RES:" + RfidConstants.ID_CATTLE + ":" + RfidConstants.RES_ERR + ":" + RfidConstants.ERR_NO_TAG + ":FW:00";

        assertDoesNotThrow(() -> noListenerController.handleIncomingSerialMessage(msg), "handleIncomingSerialMessage without viewListener must not throw NPE");
    }
}
