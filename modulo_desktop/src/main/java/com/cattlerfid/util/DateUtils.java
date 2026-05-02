package com.cattlerfid.util;

public class DateUtils {

    private DateUtils() {}

    /** YYYY-MM-DD → DD/MM/YYYY */
    public static String toDisplayDate(String isoDate) {
        if (isoDate != null && isoDate.matches("\\d{4}-\\d{2}-\\d{2}")) {
            String[] parts = isoDate.split("-");
            return parts[2] + "/" + parts[1] + "/" + parts[0];
        }
        return isoDate != null ? isoDate : "";
    }

    /** DD/MM/YYYY → YYYY-MM-DD */
    public static String toIsoDate(String displayDate) {
        if (displayDate != null && displayDate.matches("\\d{2}/\\d{2}/\\d{4}")) {
            String[] parts = displayDate.split("/");
            return parts[2] + "-" + parts[1] + "-" + parts[0];
        }
        return displayDate != null ? displayDate : "";
    }
}
