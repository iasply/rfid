package com.cattlerfid.service;

import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.config.ApiClient;
import com.cattlerfid.model.User;
import com.cattlerfid.util.RfidGenerator;
import com.google.gson.JsonObject;

import java.io.IOException;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.Optional;


public class AuthenticationService {

    private final ApiClient client;

    public AuthenticationService(ApiConfig config) {
        this(new ApiClient(config));
    }

    public AuthenticationService(ApiClient client) {
        this.client = client;
    }

    public Optional<User> authenticateByTag(String rawRfidTag) {
        if (rawRfidTag == null || rawRfidTag.isBlank()) {
            return Optional.empty();
        }

        if (!RfidGenerator.isVetTag(rawRfidTag)) {
            System.err.println("[AuthenticationService] Invalid RFID tag for vet login: " + rawRfidTag);
            return Optional.empty();
        }

        String workstationHash = client.getConfig().getWorkstationHash();
        if (workstationHash.isBlank()) {
            System.err.println("[AuthenticationService] API_WORKSTATION_HASH not set in .env");
            return Optional.empty();
        }

        String body = client.getGson().toJson(new LoginRequest(workstationHash, rawRfidTag));

        HttpRequest request = client.newRequestBuilder("/login")
                .POST(HttpRequest.BodyPublishers.ofString(body))
                .build();

        try {
            HttpResponse<String> response = client.send(request);

            if (response.statusCode() == 200) {
                JsonObject json = client.getGson().fromJson(response.body(), JsonObject.class);
                User user = client.getGson().fromJson(json.getAsJsonObject("user"), User.class);
                user.setAccessToken(json.get("access_token").getAsString());
                return Optional.of(user);
            }

            System.err.println("[AuthenticationService] Login refused. Status: " + response.statusCode());
            return Optional.empty();

        } catch (IOException | InterruptedException e) {
            System.err.println("[AuthenticationService] API unreachable: " + e.getMessage());
            if (e instanceof InterruptedException)
                Thread.currentThread().interrupt();
        }
        return Optional.empty();
    }

    public void logout(String token) {
        if (token == null || token.isBlank()) {
            return;
        }

        HttpRequest request = client.newAuthenticatedRequestBuilder("/logout", token)
                .POST(HttpRequest.BodyPublishers.noBody())
                .build();

        // Fire and forget logout
        client.sendAsync(request);
    }

    private record LoginRequest(String workstation, String tag) {
    }
}
