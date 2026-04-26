package com.cattlerfid;

import com.cattlerfid.config.ApiClient;
import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.model.Vaccine;
import com.cattlerfid.model.VaccineType;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.util.RfidGenerator;
import org.junit.jupiter.api.*;

import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Real integration test hitting the PHP API.
 * Requires the Laravel server to be running at http://127.0.0.1:8000
 */
@Tag("integration")
@TestInstance(TestInstance.Lifecycle.PER_CLASS)
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class ApiIntegrationTest {

    private ApiConfig apiConfig;
    private ApiClient apiClient;
    private AuthenticationService authService;
    private CattleApiService cattleService;
    private User currentUser;
    private String sharedTestTag;

    @BeforeAll
    void setup() {
        apiConfig = new ApiConfig(".env.test");
        apiClient = new ApiClient(apiConfig);
        authService = new AuthenticationService(apiClient);
        sharedTestTag = RfidGenerator.generateCattleTag();
        assertTrue(RfidGenerator.isValid(sharedTestTag), "Generated tag should be valid");
    }

    @Test
    @Order(1)
    @DisplayName("Should authenticate using veterinarian tag V000002")
    void testAuthentication() {
        String vetTag = "V000002";
        Optional<User> userOpt = authService.authenticateByTag(vetTag);

        assertTrue(userOpt.isPresent(), "Authentication should return a User object");
        currentUser = userOpt.get();

        assertEquals("Vet Integration Test", currentUser.getName());
        assertNotNull(currentUser.getAccessToken(), "User should have an access token");
        assertTrue(currentUser.isVeterinarian());

        cattleService = new CattleApiService(apiClient, currentUser);
    }

    @Test
    @Order(2)
    @DisplayName("Should list cattle from the API")
    void testListCattle() {
        assertNotNull(cattleService);
        List<Cattle> cattleList = cattleService.getAllCattle();
        assertNotNull(cattleList);
    }

    @Test
    @Order(3)
    @DisplayName("Should create a test cattle")
    void testCreateCattle() {
        assertNotNull(cattleService);
        Cattle c = new Cattle(sharedTestTag, "Integration Test Cow", 500.0, "2024-03-14");
        boolean success = cattleService.saveCattle(c);
        assertTrue(success);
    }

    @Test
    @Order(4)
    @DisplayName("Should register a vaccine for a test cattle")
    void testRegisterVaccine() {
        assertNotNull(cattleService);

        List<VaccineType> types = cattleService.getVaccineTypes();
        assertFalse(types.isEmpty(), "API must return at least one vaccine type");

        Vaccine v = new Vaccine();
        v.setRfidTag(sharedTestTag);
        v.setVaccineTypeId(types.get(0).getId());
        v.setCurrentWeight(180.0);
        v.setVaccinationDate("2024-03-14");

        boolean success = cattleService.saveVaccine(v);
        assertTrue(success);
    }

    @Test
    @Order(5)
    @DisplayName("Should update an existing cattle's name and weight")
    void testUpdateCattle() {
        assertNotNull(cattleService);
        Cattle c = cattleService.getCattleByTag(sharedTestTag).orElseThrow();
        c.setName("Updated Mimosa Name");
        c.setWeight(c.getWeight() + 10.0);

        boolean success = cattleService.updateCattle(c);
        assertTrue(success);

        Cattle updated = cattleService.getCattleByTag(sharedTestTag).orElseThrow();
        assertEquals("Updated Mimosa Name", updated.getName());
    }
}
