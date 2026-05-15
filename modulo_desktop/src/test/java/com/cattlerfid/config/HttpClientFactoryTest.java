package com.cattlerfid.config;

import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.io.TempDir;

import java.io.IOException;
import java.net.http.HttpClient;
import java.nio.file.Files;
import java.nio.file.Path;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Testa a criação do HttpClient via HttpClientFactory, garantindo que o SSL_TRUST_ALL seja respeitado.
 */
class HttpClientFactoryTest {

    // ── SSL_TRUST_ALL=false → HttpClient padrão (CA do sistema) ──────────

    @Test
    void givenTrustAllFalse_shouldReturnStandardHttpClient(@TempDir Path tempDir) throws IOException {
        // Arrange – .env sem SSL_TRUST_ALL (default false)
        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "API_BASE_URL=https://example.com/api\n");

        ApiConfig config = new ApiConfig(envFile.toString());

        // Act
        HttpClient client = HttpClientFactory.create(config);

        // Assert
        assertNotNull(client);
        assertFalse(config.isTrustAllCerts());
    }

    // ── SSL_TRUST_ALL=true → HttpClient com trust-all SSLContext ─────────

    @Test
    void givenTrustAllTrue_shouldReturnTrustAllHttpClient(@TempDir Path tempDir) throws IOException {
        // Arrange
        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "API_BASE_URL=https://localhost/api\n" + "SSL_TRUST_ALL=true\n");

        ApiConfig config = new ApiConfig(envFile.toString());

        // Act
        HttpClient client = HttpClientFactory.create(config);

        // Assert
        assertNotNull(client);
        assertTrue(config.isTrustAllCerts());
    }

    // ── SSL_TRUST_ALL com valor inválido → default false ─────────────────

    @Test
    void givenTrustAllInvalidValue_shouldDefaultToFalse(@TempDir Path tempDir) throws IOException {
        Path envFile = tempDir.resolve(".env");
        Files.writeString(envFile, "SSL_TRUST_ALL=yes\n");  // valor inválido

        ApiConfig config = new ApiConfig(envFile.toString());

        assertFalse(config.isTrustAllCerts());
    }

    // ── Sem .env → HttpClient padrão, sem lançar exceção ─────────────────

    @Test
    void givenNoEnvFile_shouldCreateDefaultHttpClientWithoutException() {
        ApiConfig config = new ApiConfig("/tmp/inexistente_cattle_rfid.env");

        HttpClient client = HttpClientFactory.create(config);

        assertNotNull(client);
        assertFalse(config.isTrustAllCerts());
    }
}
