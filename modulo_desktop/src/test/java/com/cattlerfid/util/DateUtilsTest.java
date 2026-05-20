package com.cattlerfid.util;

import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.assertEquals;

class DateUtilsTest {

    @Test
    void toDisplayDate_convertsIsoToDisplay() {
        assertEquals("15/01/2024", DateUtils.toDisplayDate("2024-01-15"));
    }

    @Test
    void toDisplayDate_handlesLeadingZeros() {
        assertEquals("01/06/2023", DateUtils.toDisplayDate("2023-06-01"));
    }

    @Test
    void toDisplayDate_returnsEmptyStringForNull() {
        assertEquals("", DateUtils.toDisplayDate(null));
    }

    @Test
    void toDisplayDate_returnsEmptyStringForEmptyInput() {
        assertEquals("", DateUtils.toDisplayDate(""));
    }

    @Test
    void toDisplayDate_passesThroughNonIsoInput() {
        assertEquals("not-a-date", DateUtils.toDisplayDate("not-a-date"));
    }

    @Test
    void toDisplayDate_passesThroughAlreadyDisplayFormat() {

        assertEquals("15/01/2024", DateUtils.toDisplayDate("15/01/2024"));
    }

    @Test
    void toIsoDate_convertsDisplayToIso() {
        assertEquals("2024-01-15", DateUtils.toIsoDate("15/01/2024"));
    }

    @Test
    void toIsoDate_handlesLeadingZeros() {
        assertEquals("2023-06-01", DateUtils.toIsoDate("01/06/2023"));
    }

    @Test
    void toIsoDate_returnsEmptyStringForNull() {
        assertEquals("", DateUtils.toIsoDate(null));
    }

    @Test
    void toIsoDate_returnsEmptyStringForEmptyInput() {
        assertEquals("", DateUtils.toIsoDate(""));
    }

    @Test
    void toIsoDate_passesThroughNonDisplayInput() {
        assertEquals("not-a-date", DateUtils.toIsoDate("not-a-date"));
    }

    @Test
    void roundTrip_isoToDisplayToIso() {
        String iso = "2024-06-30";
        assertEquals(iso, DateUtils.toIsoDate(DateUtils.toDisplayDate(iso)));
    }

    @Test
    void roundTrip_displayToIsoToDisplay() {
        String display = "30/06/2024";
        assertEquals(display, DateUtils.toDisplayDate(DateUtils.toIsoDate(display)));
    }
}
