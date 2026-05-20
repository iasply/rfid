package com.cattlerfid.config;

import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import java.net.http.HttpClient;
import java.security.cert.X509Certificate;
import java.time.Duration;

public class HttpClientFactory {

    private HttpClientFactory() {
    }

    public static HttpClient create(ApiConfig config) {
        HttpClient.Builder builder = HttpClient.newBuilder().connectTimeout(Duration.ofSeconds(5)).followRedirects(HttpClient.Redirect.NORMAL);

        if (config.isTrustAllCerts()) {
            System.err.println("[HttpClientFactory] ⚠  SSL_TRUST_ALL=true — " + "Configurando SSL local dev certs!");
            String certPath = config.getSslDevCertPath();
            builder.sslContext(buildDevContext(certPath));
        }

        return builder.build();
    }

    private static SSLContext buildDevContext(String certPath) {
        try {
            if (certPath == null || certPath.isBlank()) {
                System.err.println("[HttpClientFactory] ⚠ SSL_DEV_CERT_PATH nao definido. Tentando Trust-all (pode falhar no hostname verification)...");
                return buildTrustAllContext();
            }

            String path = certPath.trim();

            if (path.matches("^/[a-zA-Z]/.*")) {
                path = path.substring(1, 2).toUpperCase() + ":" + path.substring(2);
            }

            java.nio.file.Path p = java.nio.file.Paths.get(path);

            if (!p.isAbsolute()) {
                p = java.nio.file.Paths.get("").toAbsolutePath().resolve(p);
            }

            p = p.normalize();
            System.out.println("Path: " + p.toAbsolutePath());
            java.io.FileInputStream fis = new java.io.FileInputStream(p.toFile());
            java.security.cert.CertificateFactory cf = java.security.cert.CertificateFactory.getInstance("X.509");
            java.security.cert.X509Certificate caCert = (java.security.cert.X509Certificate) cf.generateCertificate(fis);

            java.security.KeyStore ks = java.security.KeyStore.getInstance(java.security.KeyStore.getDefaultType());
            ks.load(null, null);
            ks.setCertificateEntry("dev-cert", caCert);

            javax.net.ssl.TrustManagerFactory tmf = javax.net.ssl.TrustManagerFactory.getInstance(javax.net.ssl.TrustManagerFactory.getDefaultAlgorithm());
            tmf.init(ks);

            SSLContext ctx = SSLContext.getInstance("TLS");
            ctx.init(null, tmf.getTrustManagers(), new java.security.SecureRandom());
            return ctx;
        } catch (Exception e) {
            throw new RuntimeException("[HttpClientFactory] Failed to build dev SSLContext", e);
        }
    }

    private static SSLContext buildTrustAllContext() {
        try {
            TrustManager[] trustAll = new TrustManager[] { new X509TrustManager() {

                public X509Certificate[] getAcceptedIssuers() {
                    return new X509Certificate[0];
                }

                public void checkClientTrusted(X509Certificate[] c, String a) {
                }

                public void checkServerTrusted(X509Certificate[] c, String a) {
                }
            } };
            SSLContext ctx = SSLContext.getInstance("TLS");
            ctx.init(null, trustAll, new java.security.SecureRandom());
            return ctx;
        } catch (Exception e) {
            throw new RuntimeException("[HttpClientFactory] Failed to build trust-all SSLContext", e);
        }
    }
}
