package com.cattlerfid.config;

import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.io.TempDir;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Testa o carregamento de configurações do ApiConfig, com foco nas novas chaves SSL.
 */
class ApiConfigTest {

    // ── URL base ─────────────────────────────────────────────────────────

    @Test
    void givenEnvWithBaseUrl_shouldReadCorrectly(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "API_BASE_URL=https://cattle.example.com/api\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertEquals("https://cattle.example.com/api", config.getBaseUrl());
        assertEquals("https://cattle.example.com/api/login", config.url("/login"));
    }

    @Test
    void givenEnvWithoutBaseUrl_shouldUseDefault(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "");

        ApiConfig config = new ApiConfig(env.toString());

        assertEquals("http://127.0.0.1:8000/api", config.getBaseUrl());
    }

    // ── Workstation Hash ──────────────────────────────────────────────────

    @Test
    void givenEnvWithWorkstationHash_shouldReadCorrectly(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "API_WORKSTATION_HASH=WS-ABC123\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertEquals("WS-ABC123", config.getWorkstationHash());
    }

    @Test
    void givenEnvWithoutWorkstationHash_shouldReturnEmpty(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "API_BASE_URL=http://localhost/api\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertEquals("", config.getWorkstationHash());
    }

    // ── SSL_TRUST_ALL ─────────────────────────────────────────────────────

    @Test
    void givenSslTrustAllTrue_isTrustAllCertsShouldBeTrue(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "SSL_TRUST_ALL=true\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertTrue(config.isTrustAllCerts());
    }

    @Test
    void givenSslTrustAllTrueUppercase_isTrustAllCertsShouldBeTrue(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "SSL_TRUST_ALL=TRUE\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertTrue(config.isTrustAllCerts());
    }

    @Test
    void givenSslTrustAllFalse_isTrustAllCertsShouldBeFalse(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "SSL_TRUST_ALL=false\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertFalse(config.isTrustAllCerts());
    }

    @Test
    void givenNoSslTrustAll_isTrustAllCertsShouldDefaultToFalse(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "API_BASE_URL=https://example.com/api\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertFalse(config.isTrustAllCerts());
    }

    // ── Robustez ──────────────────────────────────────────────────────────

    @Test
    void givenEnvWithComments_shouldIgnoreCommentLines(@TempDir Path tempDir) throws IOException {
        Path env = writeEnv(tempDir, "# URL da API\n" + "API_BASE_URL=https://cattle.io/api\n" + "# SSL desabilitado\n" + "SSL_TRUST_ALL=false\n");

        ApiConfig config = new ApiConfig(env.toString());

        assertEquals("https://cattle.io/api", config.getBaseUrl());
        assertFalse(config.isTrustAllCerts());
    }

    @Test
    void givenMissingEnvFile_shouldUseDefaultsWithoutException() {
        // Não deve lançar exceção — apenas logar e usar defaults
        ApiConfig config = new ApiConfig("/tmp/nao_existe_cattle_rfid.env");

        assertEquals("http://127.0.0.1:8000/api", config.getBaseUrl());
        assertEquals("", config.getWorkstationHash());
        assertFalse(config.isTrustAllCerts());
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private Path writeEnv(Path dir, String content) throws IOException {
        Path file = dir.resolve(".env");
        Files.writeString(file, content);
        return file;
    }
}
