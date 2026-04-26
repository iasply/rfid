package com.cattlerfid.service;

import com.cattlerfid.config.ApiClient;
import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.model.Vaccine;
import com.cattlerfid.model.VaccineType;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;

import java.io.IOException;
import java.lang.reflect.Type;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Communicates with the Laravel API for Cattle, Vaccine, and VaccineType operations.
 */
public class CattleApiService {

    private final ApiClient client;
    private final User user;

    public CattleApiService(ApiConfig config, User user) {
        this(new ApiClient(config), user);
    }

    public CattleApiService(ApiClient client, User user) {
        this.client = client;
        this.user = user;
    }

    /** Finds a single cattle by its RFID tag content. */
    public Optional<Cattle> getCattleByTag(String rfidTag) {
        if (rfidTag == null || rfidTag.isBlank())
            return Optional.empty();

        HttpRequest request = authenticatedRequestBuilder("/cattle/" + rfidTag).GET().build();

        try {
            HttpResponse<String> response = client.send(request);
            if (response.statusCode() == 200) {
                return Optional.of(client.getGson().fromJson(response.body(), Cattle.class));
            }
        } catch (IOException | InterruptedException e) {
            handleError("Error fetching cattle by tag", e);
        }
        return Optional.empty();
    }

    /** Lists all cattle registered in the system. */
    public List<Cattle> getAllCattle() {
        HttpRequest request = authenticatedRequestBuilder("/cattle").GET().build();

        try {
            HttpResponse<String> response = client.send(request);
            if (response.statusCode() == 200) {
                JsonObject obj = client.getGson().fromJson(response.body(), JsonObject.class);
                Type listType = new TypeToken<ArrayList<Cattle>>() {}.getType();
                return client.getGson().fromJson(obj.getAsJsonArray("data"), listType);
            }
        } catch (IOException | InterruptedException e) {
            handleError("Error fetching all cattle", e);
        }
        return new ArrayList<>();
    }

    /** Lists all cattle with their vaccine count. */
    public List<Cattle> getAllCattleWithVaccines() {
        HttpRequest request = authenticatedRequestBuilder("/cattle-with-vaccines").GET().build();

        try {
            HttpResponse<String> response = client.send(request);
            if (response.statusCode() == 200) {
                JsonObject obj = client.getGson().fromJson(response.body(), JsonObject.class);
                Type listType = new TypeToken<ArrayList<Cattle>>() {}.getType();
                return client.getGson().fromJson(obj.getAsJsonArray("data"), listType);
            }
        } catch (IOException | InterruptedException e) {
            handleError("Error fetching all cattle with vaccines", e);
        }
        return new ArrayList<>();
    }

    /** Persists new cattle data to the server. */
    public boolean saveCattle(Cattle cattle) {
        String body = client.getGson().toJson(cattle);
        HttpRequest request = authenticatedRequestBuilder("/cattle")
                .POST(HttpRequest.BodyPublishers.ofString(body)).build();

        try {
            HttpResponse<String> response = client.send(request);
            return response.statusCode() == 200 || response.statusCode() == 201;
        } catch (IOException | InterruptedException e) {
            handleError("Error saving cattle", e);
            return false;
        }
    }

    /** Updates existing cattle data. */
    public boolean updateCattle(Cattle cattle) {
        String body = client.getGson().toJson(cattle);
        HttpRequest request = authenticatedRequestBuilder("/cattle/" + cattle.getId())
                .PUT(HttpRequest.BodyPublishers.ofString(body)).build();

        try {
            HttpResponse<String> response = client.send(request);
            return response.statusCode() == 200;
        } catch (IOException | InterruptedException e) {
            handleError("Error updating cattle", e);
            return false;
        }
    }

    /** Records a new vaccination event. */
    public boolean saveVaccine(Vaccine vaccine) {
        String body = client.getGson().toJson(vaccine);
        HttpRequest request = authenticatedRequestBuilder("/vaccines")
                .POST(HttpRequest.BodyPublishers.ofString(body)).build();

        try {
            HttpResponse<String> response = client.send(request);
            return response.statusCode() == 200 || response.statusCode() == 201;
        } catch (IOException | InterruptedException e) {
            handleError("Error saving vaccine", e);
            return false;
        }
    }

    /** Lists vaccines applied to a specific animal. */
    public List<Vaccine> getVaccinesByCattle(String rfidTag) {
        if (rfidTag == null || rfidTag.isBlank())
            return new ArrayList<>();

        HttpRequest request = authenticatedRequestBuilder("/vaccines?rfid_tag=" + rfidTag).GET().build();

        try {
            HttpResponse<String> response = client.send(request);
            if (response.statusCode() == 200) {
                JsonObject obj = client.getGson().fromJson(response.body(), JsonObject.class);
                Type listType = new TypeToken<ArrayList<Vaccine>>() {}.getType();
                return client.getGson().fromJson(obj.getAsJsonArray("data"), listType);
            }
        } catch (IOException | InterruptedException e) {
            handleError("Error fetching vaccines for tag: " + rfidTag, e);
        }
        return new ArrayList<>();
    }

    /**
     * Fetches all available vaccine types from the server.
     * Called once at startup so the form can show a dropdown.
     */
    public List<VaccineType> getVaccineTypes() {
        HttpRequest request = authenticatedRequestBuilder("/vaccine-types").GET().build();

        try {
            HttpResponse<String> response = client.send(request);
            if (response.statusCode() == 200) {
                JsonObject obj = client.getGson().fromJson(response.body(), JsonObject.class);
                JsonArray data = obj.getAsJsonArray("data");
                Type listType = new TypeToken<ArrayList<VaccineType>>() {}.getType();
                return client.getGson().fromJson(data, listType);
            }
        } catch (IOException | InterruptedException e) {
            handleError("Error fetching vaccine types", e);
        }
        return new ArrayList<>();
    }

    // --- helpers ---

    private HttpRequest.Builder authenticatedRequestBuilder(String path) {
        return client.newAuthenticatedRequestBuilder(path, user.getAccessToken());
    }

    private void handleError(String message, Exception e) {
        System.err.println("[CattleApiService] " + message + ": " + e.getMessage());
        if (e instanceof InterruptedException) {
            Thread.currentThread().interrupt();
        }
    }
}
