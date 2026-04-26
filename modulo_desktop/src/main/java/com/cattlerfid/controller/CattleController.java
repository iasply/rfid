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

    // View Callbacks
    private CattleViewListener viewListener;
    private Cattle currentEditingCattle;
    private String pendingWriteData = null;
    private final Consumer<String> serialListener = this::handleIncomingSerialMessage;

    public CattleController(CattleApiService apiService, SerialService serialService) {
        this.apiService = apiService;
        this.serialService = serialService;
    }

    public void setViewListener(CattleViewListener listener) {
        this.viewListener = listener;
        // Assegura que o parser de serial agora aponte pros listeners do Cattle
        if (serialService.isOpen()) {
            serialService.addMessageListener(serialListener);
        }
    }

    public void detachSerial() {
        serialService.removeMessageListener(serialListener);
    }

    // 1. Inicia um pedido para ler uma Tag
    public void requestReadTag() {
        if (!serialService.isOpen()) {
            if (viewListener != null)
                viewListener.onRfidReadError("Porta Serial não conectada.");
            return;
        }
        serialService.requestRead(RfidConstants.ID_CATTLE);
    }

    // 2. Inicia um pedido para verificar a tag pre-gravação
    public void requestWriteTag(String dataToWrite) {
        if (!serialService.isOpen()) {
            if (viewListener != null)
                viewListener.onRfidWriteError("Porta Serial não conectada.");
            return;
        }
        this.pendingWriteData = dataToWrite;
        serialService.requestRead(RfidConstants.ID_CATTLE); // Valida fisicamente primeiro
    }

    // 3. Salva os dados completos do formulario (Mocked Database/API)
    public void saveCattleData(Cattle cattle) {
        boolean success;
        if (cattle.getId() > 0) {
            success = apiService.updateCattle(cattle);
        } else {
            success = apiService.saveCattle(cattle);
        }

        if (success) {
            if (viewListener != null)
                viewListener.onApiSaveSuccess();
        } else {
            if (viewListener != null)
                viewListener.onApiSaveError("Falha ao salvar animal na base de dados (API).");
        }
    }

    // 4. Salva a vacina aplicada e atualiza o peso do animal
    public void saveVaccineData(com.cattlerfid.model.Vaccine vaccine, Cattle cattle,
            double currentWeight) {
        // O peso ja eh atualizado pelo VaccineApiController no servidor
        boolean vaccineSuccess = apiService.saveVaccine(vaccine);

        if (vaccineSuccess) {
            if (viewListener != null)
                viewListener.onApiSaveSuccess();
        } else {
            if (viewListener != null)
                viewListener.onApiSaveError("Falha ao registrar a vacina no banco de dados.");
        }
    }

    // Processa retorno do Arduino (Tanto respostas READ quanto respostas WRITE)
    protected void handleIncomingSerialMessage(String message) {
        // Ex read: RES:CATTLE:OK:TAG_BOI_100:FW:92
        // Ex read error: RES:CATTLE:ERR:NO_TAG:FW:92
        // Ex write: RES:CATTLE:OK:WROTE:FW:92

        String[] parts = message.split(":");
        if (parts.length >= 3) {
            // Valida se o pacote é para este controlador
            if (!parts[1].equals(RfidConstants.ID_CATTLE)) {
                return;
            }

            if (parts[2].equals(RfidConstants.RES_OK)) {
                if (parts.length > 3 && parts[3].equals(RfidConstants.MSG_WROTE)) {
                    if (viewListener != null)
                        viewListener.onRfidWriteSuccess();
                } else if (parts.length > 3) {
                    String readTag = parts[3].trim();
                    if (pendingWriteData != null) {
                        // Modo pre-gravação
                        if (RfidGenerator.isVetTag(readTag)) {
                            pendingWriteData = null; // aborta gravação
                            if (viewListener != null) {
                                viewListener.onRfidWriteError(
                                        "Bloqueado: Não é permitido sobrescrever uma Tag de Usuário.");
                            }
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

                // Se der erro durante a leitura pre-gravação
                if (pendingWriteData != null) {
                    pendingWriteData = null;
                    if (viewListener != null) {
                        if (cmdError.equals("NO_TAG")) {
                            viewListener.onRfidWriteError("Nenhuma Tag detectada para gravação.");
                        } else {
                            viewListener.onRfidWriteError(
                                    "Erro de leitura antes de gravar: " + cmdError);
                        }
                    }
                    return;
                }

                if (viewListener != null) {
                    if (cmdError.equals(RfidConstants.ERR_WRITE_FAILED)) {
                        viewListener.onRfidWriteError(
                                "Erro no barramento SPI ao gravar dados na Tag.");
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
    }

    private void processTagRead(String rfidTag) {
        if (!RfidGenerator.isCattleTag(rfidTag)) {
            if (RfidGenerator.isVetTag(rfidTag)) {
                if (viewListener != null) {
                    viewListener.onRfidReadError(
                            "Atenção: Você leu uma Tag de Usuário (Veterinário) ao invés de um Animal.");
                }
            } else {
                if (viewListener != null) {
                    viewListener.onRfidReadError(
                            "Formato de Tag animal inválido ou inválida para o sistema. Lido: " + rfidTag);
                }
            }
            return;
        }

        // Verifica se a Tag recemn-lida ja existe no banco principal
        Optional<Cattle> existing = apiService.getCattleByTag(rfidTag);

        if (existing.isPresent()) {
            currentEditingCattle = existing.get();
            if (viewListener != null)
                viewListener.onRfidReadSuccess(currentEditingCattle, false);
        } else {
            if (viewListener != null)
                viewListener.onRfidReadError(
                        "Animal não encontrado na base de dados. Por favor, cadastre-o primeiro.");
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
        void onRfidReadSuccess(Cattle cattle, boolean isNew);

        void onRfidReadError(String message);

        void onRfidWriteSuccess();

        void onRfidWriteError(String message);

        void onApiSaveSuccess();

        void onApiSaveError(String message);
    }
}
