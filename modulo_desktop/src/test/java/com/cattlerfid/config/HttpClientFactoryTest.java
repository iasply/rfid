package com.cattlerfid.config;

import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.io.TempDir;

import java.io.IOException;
import java.net.http.HttpClient;
import java.nio.file.Files;
import java.nio.file.Path;

import static org.junit.jupiter.api.Assertions.*;

class HttpClientFactoryTest {

    @Test
    void givenTrustAllFalse_shouldReturnStandardHttpClient(@TempDir Path tempDir) throws IOException {

        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "API_BASE_URL=https://example.com/api\n");

        ApiConfig config = new ApiConfig(envFile.toString());

        HttpClient client = HttpClientFactory.create(config);

        assertNotNull(client);
        assertFalse(config.isTrustAllCerts());
    }

    @Test
    void givenTrustAllTrue_shouldReturnTrustAllHttpClient(@TempDir Path tempDir) throws IOException {

        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "API_BASE_URL=https://localhost/api\n" + "SSL_TRUST_ALL=true\n");

        ApiConfig config = new ApiConfig(envFile.toString());

        HttpClient client = HttpClientFactory.create(config);

        assertNotNull(client);
        assertTrue(config.isTrustAllCerts());
    }

    @Test
    void givenTrustAllInvalidValue_shouldDefaultToFalse(@TempDir Path tempDir) throws IOException {
        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "SSL_TRUST_ALL=yes\n");

        ApiConfig config = new ApiConfig(envFile.toString());

        assertFalse(config.isTrustAllCerts());
    }

    @Test
    void givenNoEnvFile_shouldCreateDefaultHttpClientWithoutException() {
        ApiConfig config = new ApiConfig("/tmp/inexistente_cattle_rfid.env");

        HttpClient client = HttpClientFactory.create(config);

        assertNotNull(client);
        assertFalse(config.isTrustAllCerts());
    }
}
