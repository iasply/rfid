package com.cattlerfid;

import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.controller.ConnectionController;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.view.ApplicationFrame;
import com.cattlerfid.view.ConnectionPanel;
import com.formdev.flatlaf.FlatLightLaf;

import javax.swing.*;

public class Main {
    public static void main(String[] args) {
        try {
            UIManager.setLookAndFeel(new FlatLightLaf());
        } catch (Exception e) {
            System.err.println("[Main] Failed to apply FlatLaf look and feel: " + e.getMessage());
        }

        SwingUtilities.invokeLater(() -> {
            ApiConfig apiConfig = new ApiConfig();
            AuthenticationService authService = new AuthenticationService(apiConfig);
            SerialService serialService = new SerialService();

            ConnectionController connectionController = new ConnectionController(serialService);

            ApplicationFrame appFrame = new ApplicationFrame();
            ConnectionPanel connectionPanel = new ConnectionPanel(connectionController, authService,
                    apiConfig, appFrame);

            appFrame.setVisible(true);
            appFrame.showPanel("Connection", connectionPanel);
        });
    }
}
