package com.cattlerfid.model;

import com.cattlerfid.util.RfidGenerator;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.assertEquals;

class CattleTest {

    @Test
    void testCattleCreationAndGetters() {
        String date = "2026-03-07";
        String tag = RfidGenerator.generateCattleTag();
        Cattle cattle = new Cattle(tag, "Boi Bandido", 450.5, date);

        assertEquals(tag, cattle.getRfidTag());
        assertEquals("Boi Bandido", cattle.getName());
        assertEquals(450.5, cattle.getWeight());
        assertEquals(date, cattle.getRegistrationDate());
    }

    @Test
    void testCattleSetters() {
        Cattle cattle = new Cattle();
        String tag = RfidGenerator.generateCattleTag();

        cattle.setRfidTag(tag);
        cattle.setName("Mimosa");
        cattle.setWeight(300.0);
        String newDate = "2026-01-01";
        cattle.setRegistrationDate(newDate);

        assertEquals(tag, cattle.getRfidTag());
        assertEquals("Mimosa", cattle.getName());
        assertEquals(300.0, cattle.getWeight());
        assertEquals(newDate, cattle.getRegistrationDate());
    }

    @Test
    void testCattleDeserializationWithVaccinesCount() {
        String tag = RfidGenerator.generateCattleTag();
        String json = "{\"rfid_tag\":\"" + tag + "\",\"name\":\"Boi Bandido\",\"weight\":450.5,\"registration_date\":\"2026-03-07\",\"vaccines_count\":5}";
        com.google.gson.Gson gson = new com.google.gson.Gson();
        Cattle cattle = gson.fromJson(json, Cattle.class);

        assertEquals(tag, cattle.getRfidTag());
        assertEquals(5, cattle.getVaccinesCount());
    }
}
