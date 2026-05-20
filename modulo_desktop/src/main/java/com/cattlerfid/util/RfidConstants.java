package com.cattlerfid.util;

public class RfidConstants {

    public static final String ID_CATTLE = "CATTLE";
    public static final String ID_CONN = "CONN";
    public static final String ID_LOGIN = "LOGIN";

    public static final String CMD_READ = "READ";
    public static final String CMD_WRITE = "WRITE";

    public static final String RES_OK = "OK";
    public static final String RES_ERR = "ERR";

    public static final String ERR_NO_TAG = "NO_TAG";
    public static final String ERR_AUTH = "AUTH";
    public static final String ERR_WRITE_FAILED = "WRITE_FAILED";
    public static final String ERR_INVALID_CMD = "INVALID_CMD";

    public static final String MSG_WROTE = "WROTE";

    public static final int SERIAL_READ_TIMEOUT_MS = 2500;

    private RfidConstants() {

    }
}
