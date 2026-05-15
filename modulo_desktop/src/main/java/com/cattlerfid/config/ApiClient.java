package com.cattlerfid.config;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;

import java.io.IOException;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.concurrent.CompletableFuture;

/**
 * Handles API communication, logging, and JSON serialization.
 */
public class ApiClient {

    private final ApiConfig config;
    private final HttpClient http;
    private final Gson gson;

    public ApiClient(ApiConfig config) {
        this(config, HttpClientFactory.create(config));
    }

    public ApiClient(ApiConfig config, HttpClient http) {
        this.config = config;
        this.http = http;
        this.gson = new GsonBuilder().setPrettyPrinting().create();
    }

    public HttpResponse<String> send(HttpRequest request) throws IOException, InterruptedException {
        logRequest(request);
        HttpResponse<String> response = http.send(request, HttpResponse.BodyHandlers.ofString());
        logResponse(response);
        return response;
    }

    public CompletableFuture<HttpResponse<String>> sendAsync(HttpRequest request) {
        logRequest(request);
        return http.sendAsync(request, HttpResponse.BodyHandlers.ofString()).thenApply(response -> {
            logResponse(response);
            return response;
        });
    }

    public HttpRequest.Builder newRequestBuilder(String path) {
        return HttpRequest.newBuilder().uri(java.net.URI.create(config.url(path))).header("Content-Type", "application/json").header("Accept", "application/json");
    }

    public HttpRequest.Builder newAuthenticatedRequestBuilder(String path, String token) {
        return newRequestBuilder(path).header("Authorization", "Bearer " + token);
    }

    public Gson getGson() {
        return gson;
    }

    public ApiConfig getConfig() {
        return config;
    }

    private void logRequest(HttpRequest request) {
        String method = request.method();
        String uri = request.uri().toString();
        String headers = request.headers().map().toString();

        if (headers.contains("Authorization=[Bearer ")) {
            headers = headers.replaceAll("Authorization=\\[Bearer [^\\]]+\\]", "Authorization=[Bearer ********]");
        }

        System.out.println("\n[API REQUEST]");
        System.out.println("Method: " + method);
        System.out.println("URI:    " + uri);
        System.out.println("Headers: " + headers);

        request.bodyPublisher().ifPresent(publisher -> {
            java.net.http.HttpResponse.BodySubscriber<String> subscriber = java.net.http.HttpResponse.BodySubscribers.ofString(java.nio.charset.StandardCharsets.UTF_8);
            publisher.subscribe(new java.util.concurrent.Flow.Subscriber<java.nio.ByteBuffer>() {

                @Override
                public void onSubscribe(java.util.concurrent.Flow.Subscription s) {
                    s.request(Long.MAX_VALUE);
                }

                @Override
                public void onNext(java.nio.ByteBuffer item) {
                    subscriber.onNext(java.util.List.of(item));
                }

                @Override
                public void onError(Throwable t) {
                }

                @Override
                public void onComplete() {
                    subscriber.onComplete();
                }
            });
            try {
                String body = subscriber.getBody().toCompletableFuture().join();
                System.out.println("Body:    " + body);
            } catch (Exception e) {
                System.out.println("Body:    (Could not log body: " + e.getMessage() + ")");
            }
        });
    }

    private void logResponse(HttpResponse<String> response) {
        System.out.println("\n[API RESPONSE]");
        System.out.println("Status: " + response.statusCode());
        System.out.println("Body:   " + response.body());
        System.out.println("----------------------------------\n");
    }
}
