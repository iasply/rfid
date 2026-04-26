package com.cattlerfid;

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
            e.printStackTrace();
        }

        SwingUtilities.invokeLater(() -> {

            com.cattlerfid.config.ApiConfig apiConfig = new com.cattlerfid.config.ApiConfig();
            AuthenticationService authService = new AuthenticationService(apiConfig);
            SerialService serialService = new SerialService();

            ConnectionController connectionController = new ConnectionController(serialService);

            ApplicationFrame appFrame = new ApplicationFrame();
            ConnectionPanel connectionPanel = new ConnectionPanel(connectionController, authService,
                    apiConfig, appFrame);

            appFrame.setVisible(true);
            appFrame.showPanel("Connection", connectionPanel);

            System.out.println("Sistema Modulo Desktop iniciado (Single-Window Mode).");
        });
    }
}
