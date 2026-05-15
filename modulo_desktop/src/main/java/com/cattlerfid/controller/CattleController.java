package com.cattlerfid.controller;

import com.cattlerfid.model.Cattle;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.RfidConstants;
import com.cattlerfid.util.RfidGenerator;

import java.util.Optional;
import java.util.function.Consumer;

public class CattleController {

    private final CattleApiService apiService;
    private final SerialService serialService;

    private CattleViewListener viewListener = new CattleViewListener() {

        public void onRfidReadSuccess(Cattle cattle) {}

        public void onRfidReadError(String message) {}

        public void onRfidWriteSuccess() {}

        public void onRfidWriteError(String message) {}

        public void onApiSaveSuccess() {}

        public void onApiSaveError(String message) {}
    };
    private Cattle currentEditingCattle;
    private String pendingWriteData = null;
    private final Consumer<String> serialListener = this::handleIncomingSerialMessage;

    public CattleController(CattleApiService apiService, SerialService serialService) {
        this.apiService = apiService;
        this.serialService = serialService;
    }

    public void setViewListener(CattleViewListener listener) {
        this.viewListener = listener;
        if (serialService.isOpen()) {
            serialService.addMessageListener(serialListener);
        }
    }

    public void detachSerial() {
        serialService.removeMessageListener(serialListener);
    }

    public void requestReadTag() {
        if (!serialService.isOpen()) {
            viewListener.onRfidReadError("Porta Serial não conectada.");
            return;
        }
        serialService.requestRead(RfidConstants.ID_CATTLE);
    }

    public void requestWriteTag(String dataToWrite) {
        if (!serialService.isOpen()) {
            viewListener.onRfidWriteError("Porta Serial não conectada.");
            return;
        }
        this.pendingWriteData = dataToWrite;
        serialService.requestRead(RfidConstants.ID_CATTLE);
    }

    public void saveCattleData(Cattle cattle) {
        boolean success;
        if (cattle.getId() > 0) {
            success = apiService.updateCattle(cattle);
        } else {
            success = apiService.saveCattle(cattle);
        }

        if (success) {
            viewListener.onApiSaveSuccess();
        } else {
            viewListener.onApiSaveError("Falha ao salvar animal na base de dados (API).");
        }
    }

    public void saveVaccineData(com.cattlerfid.model.Vaccine vaccine, Cattle cattle, double currentWeight) {
        boolean vaccineSuccess = apiService.saveVaccine(vaccine);

        if (vaccineSuccess) {
            viewListener.onApiSaveSuccess();
        } else {
            viewListener.onApiSaveError("Falha ao registrar a vacina no banco de dados.");
        }
    }

    protected void handleIncomingSerialMessage(String message) {
        String[] parts = message.split(":");
        if (parts.length >= 3) {
            if (!parts[1].equals(RfidConstants.ID_CATTLE)) {
                return;
            }

            if (parts[2].equals(RfidConstants.RES_OK)) {
                if (parts.length > 3 && parts[3].equals(RfidConstants.MSG_WROTE)) {
                    viewListener.onRfidWriteSuccess();
                } else if (parts.length > 3) {
                    String readTag = parts[3].trim();
                    if (pendingWriteData != null) {
                        if (RfidGenerator.isVetTag(readTag)) {
                            pendingWriteData = null;
                            viewListener.onRfidWriteError("Bloqueado: Não é permitido sobrescrever uma Tag de Usuário.");
                        } else {
                            serialService.requestWrite(RfidConstants.ID_CATTLE, pendingWriteData);
                            pendingWriteData = null;
                        }
                    } else {
                        processTagRead(readTag);
                    }
                }
            } else if (parts[2].equals(RfidConstants.RES_ERR)) {
                String cmdError = parts[3];

                if (pendingWriteData != null) {
                    pendingWriteData = null;
                    if (cmdError.equals(RfidConstants.ERR_NO_TAG)) {
                        viewListener.onRfidWriteError("Nenhuma Tag detectada para gravação.");
                    } else {
                        viewListener.onRfidWriteError("Erro de leitura antes de gravar: " + cmdError);
                    }
                    return;
                }

                if (cmdError.equals(RfidConstants.ERR_WRITE_FAILED)) {
                    viewListener.onRfidWriteError("Erro no barramento SPI ao gravar dados na Tag.");
                } else if (cmdError.equals(RfidConstants.ERR_NO_TAG)) {
                    viewListener.onRfidReadError("Nenhuma Tag detectada.");
                } else if (cmdError.equals(RfidConstants.ERR_AUTH)) {
                    viewListener.onRfidReadError("Erro de autenticação da Tag.");
                } else {
                    viewListener.onRfidReadError("Erro desconhecido: " + cmdError);
                }
            }
        }
    }

    private void processTagRead(String rfidTag) {
        if (!RfidGenerator.isCattleTag(rfidTag)) {
            if (RfidGenerator.isVetTag(rfidTag)) {
                viewListener.onRfidReadError("Atenção: Você leu uma Tag de Usuário (Veterinário) ao invés de um Animal.");
            } else {
                viewListener.onRfidReadError("Formato de Tag animal inválido ou inválida para o sistema. Lido: " + rfidTag);
            }
            return;
        }

        Optional<Cattle> existing = apiService.getCattleByTag(rfidTag);

        if (existing.isPresent()) {
            currentEditingCattle = existing.get();
            viewListener.onRfidReadSuccess(currentEditingCattle);
        } else {
            viewListener.onRfidReadError("Animal não encontrado na base de dados. Por favor, cadastre-o primeiro.");
        }
    }

    public Cattle getCurrentEditingCattle() {
        return currentEditingCattle;
    }

    public SerialService getSerialService() {
        return serialService;
    }

    public CattleApiService getApiService() {
        return apiService;
    }

    public interface CattleViewListener {

        void onRfidReadSuccess(Cattle cattle);

        void onRfidReadError(String message);

        void onRfidWriteSuccess();

        void onRfidWriteError(String message);

        void onApiSaveSuccess();

        void onApiSaveError(String message);
    }
}
