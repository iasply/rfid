package com.cattlerfid.util;

import java.util.UUID;

public class RfidGenerator {

    private static final int RFID_RANDOM_PART_LENGTH = 10;
    private static final int RFID_MAX_LENGTH = 16;

    public static String generateCattleTag() {
        return "C" + UUID.randomUUID().toString().replace("-", "").substring(0, RFID_RANDOM_PART_LENGTH).toUpperCase();
    }

    public static String generateVetTag() {
        return "V" + UUID.randomUUID().toString().replace("-", "").substring(0, RFID_RANDOM_PART_LENGTH).toUpperCase();
    }

    public static boolean isValid(String rfid) {
        if (rfid == null || rfid.isEmpty()) {
            return false;
        }

        if (rfid.length() < 2 || rfid.length() > RFID_MAX_LENGTH) {
            return false;
        }

        char prefix = Character.toUpperCase(rfid.charAt(0));
        if (prefix != 'C' && prefix != 'V') {
            return false;
        }

        return rfid.matches("^[a-zA-Z0-9]+$");
    }

    public static boolean isCattleTag(String rfid) {
        return isValid(rfid) && Character.toUpperCase(rfid.charAt(0)) == 'C';
    }

    public static boolean isVetTag(String rfid) {
        return isValid(rfid) && Character.toUpperCase(rfid.charAt(0)) == 'V';
    }
}
