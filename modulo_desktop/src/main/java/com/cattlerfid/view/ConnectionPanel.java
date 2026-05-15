package com.cattlerfid.view;

import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.controller.ConnectionController;
import com.cattlerfid.controller.LoginController;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.DebounceUtil;
import com.cattlerfid.util.RfidConstants;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;

public class ConnectionPanel extends JPanel implements ConnectionController.ConnectionViewListener {

    private final ConnectionController controller;
    private final AuthenticationService authService;
    private final ApiConfig apiConfig;
    private final NavigationManager navManager;

    private JLabel statusLabel;
    private JComboBox<String> portSelector;
    private JButton connectPortButton;
    private JButton disconnectButton;
    private JButton testReadButton;

    public ConnectionPanel(ConnectionController controller, AuthenticationService authService, ApiConfig apiConfig, NavigationManager navManager) {
        this.controller = controller;
        this.authService = authService;
        this.apiConfig = apiConfig;
        this.navManager = navManager;
        this.controller.setViewListener(this);

        setupUI();
    }

    private void setupUI() {
        setLayout(new BorderLayout(10, 10));
        // Panels do not have setTitle, pack, etc.

        // Background
        setBackground(UIStyles.BACKGROUND);

        // Header - Dark Emerald
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.PRIMARY_DARK);
        JLabel titleLabel = UIStyles.createTitleLabel("Configuração de Hardware");
        titleLabel.setForeground(Color.WHITE); // Contrast
        headerPanel.add(titleLabel, BorderLayout.CENTER);

        JButton logButton = new JButton("Ver Logs Serial");
        logButton.setFont(new Font("Arial", Font.PLAIN, 10));
        logButton.addActionListener(DebounceUtil.debounce(e -> {
            SerialLogFrame logFrame = new SerialLogFrame(controller.getSerialService());
            logFrame.setVisible(true);
        }, DebounceUtil.NAV_MS));
        headerPanel.add(logButton, BorderLayout.EAST);
        add(headerPanel, BorderLayout.NORTH);

        // Center Panel (Config e Status)
        JPanel centerPanel = new JPanel(new GridLayout(3, 1, 5, 15));
        centerPanel.setBorder(UIStyles.createCardBorder());
        centerPanel.setBackground(Color.WHITE);

        JPanel portPanel = new JPanel(new FlowLayout(FlowLayout.CENTER, 15, 10));
        portPanel.setBackground(Color.WHITE);

        JLabel portLabel = new JLabel("Porta Serial (Arduino):");
        portLabel.setFont(UIStyles.LABEL_FONT);
        portPanel.add(portLabel);

        portSelector = new JComboBox<>(SerialService.getAvailablePorts());
        portSelector.setFont(UIStyles.BODY_FONT);
        portPanel.add(portSelector);

        JCheckBox simCheckBox = new JCheckBox("Simular Hardware (Modo Terminal)");
        simCheckBox.setBackground(Color.WHITE);
        simCheckBox.setFont(UIStyles.BODY_FONT);
        simCheckBox.addActionListener(e -> controller.setSimulationMode(simCheckBox.isSelected()));
        portPanel.add(simCheckBox);

        connectPortButton = UIStyles.createPrimaryButton("Conectar");
        connectPortButton.setPreferredSize(new Dimension(130, 35));
        connectPortButton.addActionListener(DebounceUtil.debounce(e -> connectSerial()));
        portPanel.add(connectPortButton);

        disconnectButton = UIStyles.createBackButton("Desconectar");
        disconnectButton.setPreferredSize(new Dimension(130, 35));
        disconnectButton.setEnabled(false);
        disconnectButton.addActionListener(DebounceUtil.debounce(e -> controller.disconnectSerial()));
        portPanel.add(disconnectButton);

        centerPanel.add(portPanel);

        statusLabel = new JLabel("Aguardando conexão...", SwingConstants.CENTER);
        statusLabel.setFont(UIStyles.BODY_FONT);
        statusLabel.setForeground(UIStyles.TEXT_DARK);
        centerPanel.add(statusLabel);

        JPanel testPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        testPanel.setBackground(Color.WHITE);
        testReadButton = UIStyles.createPrimaryButton("Realizar Teste Inicial de Leitura");
        testReadButton.setPreferredSize(new Dimension(300, 45));
        testReadButton.setEnabled(false);
        testReadButton.addActionListener(DebounceUtil.debounce(e -> controller.requestTestRead()));
        testPanel.add(testReadButton);

        centerPanel.add(testPanel);

        // Wrapper for true centering
        JPanel wrapperPanel = new JPanel(new GridBagLayout());
        wrapperPanel.setBackground(UIStyles.BACKGROUND);
        wrapperPanel.add(centerPanel);

        add(wrapperPanel, BorderLayout.CENTER);
    }

    private void connectSerial() {
        if (portSelector.getSelectedItem() != null) {
            String port = portSelector.getSelectedItem().toString();
            statusLabel.setText("Conectando na " + port + "...");
            connectPortButton.setEnabled(false);
            portSelector.setEnabled(false);

            // Operação assíncrona para não freezar a Interface Gráfica com a API Serial
            // travando em IO
            new Thread(() -> {
                controller.startSerialConnection(port);
            }).start();
        }
    }

    @Override
    public void onSerialConnected() {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Arduino Conectado! Faça um teste de leitura.");
            statusLabel.setForeground(UIStyles.PRIMARY); // Premium Emerald
            connectPortButton.setEnabled(false);
            portSelector.setEnabled(false);
            disconnectButton.setEnabled(true);
            testReadButton.setEnabled(true);
        });
    }

    @Override
    public void onSerialDisconnected() {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Desconectado. Aguardando conexão...");
            statusLabel.setForeground(Color.DARK_GRAY);
            connectPortButton.setEnabled(true);
            portSelector.setEnabled(true);
            disconnectButton.setEnabled(false);
            testReadButton.setEnabled(false);
            testReadButton.setText("Realizar Teste Inicial de Leitura");
        });
    }

    @Override
    public void onSerialError(String message) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText(message);
            statusLabel.setForeground(Color.RED);

            if (message.startsWith("Não foi possível conectar")) {
                connectPortButton.setEnabled(true);
                portSelector.setEnabled(true);
                if (disconnectButton != null)
                    disconnectButton.setEnabled(false);
            }

            testReadButton.setEnabled(true);
            testReadButton.setText("Realizar Teste Inicial de Leitura");
            JOptionPane.showMessageDialog(this, message, "Erro", JOptionPane.ERROR_MESSAGE);
        });
    }

    @Override
    public void onWaitingForTestTag() {
        SwingUtilities.invokeLater(() -> {
            testReadButton.setText("Lendo... Aproxime qualquer tag");
            statusLabel.setText("Aguardando leitura do RFID (Timeout " + (RfidConstants.SERIAL_READ_TIMEOUT_MS / 1000.0) + "s)...");
            statusLabel.setForeground(Color.BLUE);
        });
    }

    @Override
    public void onTestTagReadSuccess(String tagContent) {
        SwingUtilities.invokeLater(() -> {
            JOptionPane.showMessageDialog(this, "Teste concluído com sucesso!\nConteúdo Lido: " + tagContent + "\nAvançando para o Login.", "Hardware Validado", JOptionPane.INFORMATION_MESSAGE);

            controller.detachSerial();

            LoginController loginController = new LoginController(authService, controller.getSerialService());
            LoginPanel loginPanel = new LoginPanel(loginController, apiConfig, navManager);

            navManager.showPanel("Login", loginPanel);

            // Religa o canal Serial ouvindo direto pro LoginController
            loginController.attachToActiveSerial();
        });
    }
}
