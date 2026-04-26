package com.cattlerfid.service;

import com.cattlerfid.config.ApiClient;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.model.Vaccine;
import com.cattlerfid.model.VaccineType;
import com.google.gson.Gson;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.io.IOException;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
public class CattleApiServiceTest {

    @Mock
    private ApiClient apiClient;

    @Mock
    private User user;

    @Mock
    private HttpResponse<String> httpResponse;

    private CattleApiService apiService;

    private final Gson gson = new Gson();

    @BeforeEach
    void setUp() {
        lenient().when(user.getAccessToken()).thenReturn("fake_token");
        lenient().when(apiClient.getGson()).thenReturn(gson);
        apiService = new CattleApiService(apiClient, user);
    }

    @Test
    @DisplayName("Should fetch cattle by tag successfully")
    void should_fetch_cattle_by_tag_successfully() throws IOException, InterruptedException {
        String tag = "C001";
        String json = "{\"rfid_tag\":\"C001\",\"name\":\"Mimosa\"}";

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(200);
        when(httpResponse.body()).thenReturn(json);

        Optional<Cattle> result = apiService.getCattleByTag(tag);

        assertTrue(result.isPresent());
        assertEquals("Mimosa", result.get().getName());
    }

    @Test
    @DisplayName("Should return all cattle")
    void should_return_all_cattle() throws IOException, InterruptedException {
        String json = "{\"data\": [{\"rfid_tag\":\"C001\"}, {\"rfid_tag\":\"C002\"}]}";

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(200);
        when(httpResponse.body()).thenReturn(json);

        List<Cattle> result = apiService.getAllCattle();

        assertEquals(2, result.size());
    }

    @Test
    @DisplayName("Should save cattle successfully")
    void should_save_cattle_successfully() throws IOException, InterruptedException {
        Cattle cattle = new Cattle("C001", "Mimosa", 400.0, "2024-03-14");

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(201);

        boolean result = apiService.saveCattle(cattle);

        assertTrue(result);
    }

    @Test
    @DisplayName("Should save vaccine successfully")
    void should_save_vaccine_successfully() throws IOException, InterruptedException {
        Vaccine vaccine = new Vaccine();
        vaccine.setRfidTag("C001");
        vaccine.setVaccineTypeId(1L);

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(201);

        boolean result = apiService.saveVaccine(vaccine);

        assertTrue(result);
    }

    @Test
    @DisplayName("Should fetch vaccine types successfully")
    void should_fetch_vaccine_types_successfully() throws IOException, InterruptedException {
        String json = "{\"data\": [{\"id\":1,\"name\":\"Febre Aftosa\",\"interval_days\":180}, {\"id\":2,\"name\":\"Brucelose\",\"interval_days\":365}]}";

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(200);
        when(httpResponse.body()).thenReturn(json);

        List<VaccineType> result = apiService.getVaccineTypes();

        assertEquals(2, result.size());
        assertEquals("Febre Aftosa", result.get(0).getName());
        assertEquals(180, result.get(0).getIntervalDays());
    }
}
