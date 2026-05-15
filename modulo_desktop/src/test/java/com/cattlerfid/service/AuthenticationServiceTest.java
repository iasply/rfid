package com.cattlerfid.service;

import com.cattlerfid.config.ApiClient;
import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.model.User;
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
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
public class AuthenticationServiceTest {

    private final Gson gson = new Gson();
    @Mock
    private ApiClient apiClient;
    @Mock
    private ApiConfig apiConfig;
    @Mock
    private HttpResponse<String> httpResponse;
    private AuthenticationService authService;

    @BeforeEach
    void setUp() {
        lenient().when(apiClient.getGson()).thenReturn(gson);
        lenient().when(apiClient.getConfig()).thenReturn(apiConfig);
        authService = new AuthenticationService(apiClient);
    }

    @Test
    @DisplayName("Should return empty when tag is null")
    void should_return_empty_when_tag_is_null() {
        Optional<User> result = authService.authenticateByTag(null);
        assertTrue(result.isEmpty());
    }

    @Test
    @DisplayName("Should return user when login is successful")
    void should_return_user_when_login_successful() throws IOException, InterruptedException {
        String vetTag = "V000001";
        String workstation = "hash123";
        String successJson = "{\"user\": {\"id\": 1, \"name\": \"Dr. Vet\", \"is_veterinarian\": true}, \"access_token\": \"token123\"}";

        when(apiConfig.getWorkstationHash()).thenReturn(workstation);
        when(apiClient.newRequestBuilder(anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(200);
        when(httpResponse.body()).thenReturn(successJson);

        Optional<User> result = authService.authenticateByTag(vetTag);

        assertTrue(result.isPresent());
        User user = result.get();
        assertEquals("Dr. Vet", user.getName());
        assertEquals("token123", user.getAccessToken());
    }

    @Test
    @DisplayName("Should return empty when login fails")
    void should_return_empty_when_login_fails() throws IOException, InterruptedException {
        when(apiConfig.getWorkstationHash()).thenReturn("hash");
        when(apiClient.newRequestBuilder(anyString())).thenReturn(HttpRequest.newBuilder().uri(java.net.URI.create("http://test.com")));
        when(apiClient.send(any(HttpRequest.class))).thenReturn(httpResponse);
        when(httpResponse.statusCode()).thenReturn(401);

        Optional<User> result = authService.authenticateByTag("V000001");

        assertTrue(result.isEmpty());
    }
}
