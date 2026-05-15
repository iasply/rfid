package com.cattlerfid.service;

import com.cattlerfid.config.ApiClient;
import com.cattlerfid.model.*;
import com.google.gson.Gson;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.io.IOException;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.lenient;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
public class CattleApiServiceTest {

    private final Gson gson = new Gson();
    @Mock
    private ApiClient apiClient;
    @Mock
    private User user;
    @Mock
    private HttpResponse<String> httpResponse;
    private CattleApiService apiService;

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
    @DisplayName("Should parse paginated cattle using meta object from Laravel ResourceCollection")
    void should_parse_paginated_cattle_from_meta() throws IOException, InterruptedException {
        String json = "{" + "\"data\":[{\"rfid_tag\":\"C001\",\"name\":\"Mimosa\",\"weight\":400.0,\"registration_date\":\"2024-01-15\",\"vaccines_count\":2}],"
                + "\"links\":{\"first\":\"http://test.com?page=1\",\"last\":\"http://test.com?page=3\",\"prev\":null,\"next\":\"http://test.com?page=2\"},"
                + "\"meta\":{\"current_page\":1,\"from\":1,\"last_page\":3,\"per_page\":15,\"to\":15,\"total\":42}" + "}";

        when(apiClient.newAuthenticatedRequestBuilder(anyString(), anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(200);
        when(httpResponse.body()).thenReturn(json);

        PagedResult<Cattle> result = apiService.getCattleWithVaccinesPaginated(1);

        assertEquals(1, result.getData().size());
        assertEquals("Mimosa", result.getData().get(0).getName());
        assertEquals(1, result.getCurrentPage());
        assertEquals(3, result.getLastPage());
        assertEquals(42, result.getTotal());
        assertEquals(2, result.getData().get(0).getVaccinesCount());
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
