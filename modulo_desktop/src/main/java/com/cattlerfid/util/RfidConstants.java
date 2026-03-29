package com.cattlerfid.util;

/**
 * Constantes para comunicação com o módulo Arduino via Serial.
 */
public class RfidConstants {

    // Identificadores de Pacote (UIDs)
    public static final String ID_CATTLE = "CATTLE";
    public static final String ID_CONN = "CONN";
    public static final String ID_LOGIN = "LOGIN";

    // Comandos
    public static final String CMD_READ = "READ";
    public static final String CMD_WRITE = "WRITE";

    // Status de Resposta
    public static final String RES_OK = "OK";
    public static final String RES_ERR = "ERR";

    // Erros Comuns
    public static final String ERR_NO_TAG = "NO_TAG";
    public static final String ERR_AUTH = "AUTH";
    public static final String ERR_WRITE_FAILED = "WRITE_FAILED";
    public static final String ERR_INVALID_CMD = "INVALID_CMD";

    // Outros
    public static final String MSG_WROTE = "WROTE";

    private RfidConstants() {
        // Impedir instanciação
    }
}
