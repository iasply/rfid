package com.cattlerfid.util;

import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;

class RfidGeneratorTest {

    @Test
    void should_generate_valid_cattle_tag() {
        String tag = RfidGenerator.generateCattleTag();
        assertTrue(RfidGenerator.isValid(tag), "Generated cattle tag should be valid");
        assertTrue(tag.startsWith("C"), "Cattle tag should start with C");
    }

    @Test
    void should_generate_valid_vet_tag() {
        String tag = RfidGenerator.generateVetTag();
        assertTrue(RfidGenerator.isValid(tag), "Generated vet tag should be valid");
        assertTrue(tag.startsWith("V"), "Vet tag should start with V");
    }

    @Test
    void should_validate_various_tags() {

        assertTrue(RfidGenerator.isValid("C12345"), "Short valid tag");
        assertTrue(RfidGenerator.isValid("VABC12345678901"), "Long valid tag (16 chars)");

        assertTrue(RfidGenerator.isCattleTag("C12345"), "Should be cattle tag");
        assertFalse(RfidGenerator.isCattleTag("V12345"), "Should not be cattle tag");
        assertTrue(RfidGenerator.isVetTag("V12345"), "Should be vet tag");
        assertFalse(RfidGenerator.isVetTag("C12345"), "Should not be vet tag");

        assertFalse(RfidGenerator.isValid(""), "Empty tag");
        assertFalse(RfidGenerator.isValid(null), "Null tag");
        assertFalse(RfidGenerator.isValid("A123"), "Wrong prefix");
        assertFalse(RfidGenerator.isValid("C"), "Too short");
        assertFalse(RfidGenerator.isValid("C1234567890123456"), "Too long (17 chars)");
        assertFalse(RfidGenerator.isValid("C123-456"), "Invalid characters");
    }
}
