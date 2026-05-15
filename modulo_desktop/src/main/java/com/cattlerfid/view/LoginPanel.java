package com.cattlerfid.view;

import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.controller.CattleController;
import com.cattlerfid.controller.ConnectionController;
import com.cattlerfid.controller.LoginController;
import com.cattlerfid.model.User;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.util.DebounceUtil;
import com.cattlerfid.util.RfidConstants;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;

public class LoginPanel extends JPanel implements LoginController.LoginViewListener {

    private final LoginController controller;
    private final ApiConfig apiConfig;
    private final NavigationManager navManager;

    private JLabel statusLabel;
    private JButton readCardButton;

    public LoginPanel(LoginController controller, ApiConfig apiConfig, NavigationManager navManager) {
        this.controller = controller;
        this.apiConfig = apiConfig;
        this.navManager = navManager;
        this.controller.setViewListener(this);

        setupUI();
    }

    private void setupUI() {
        setLayout(new BorderLayout(10, 10));

        // Background
        setBackground(UIStyles.BACKGROUND);

        // Header - Dark Emerald
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.PRIMARY_DARK); // Darker top
        JLabel titleLabel = UIStyles.createTitleLabel("Acesso via Crachá RFID");
        titleLabel.setForeground(Color.WHITE); // Contrast for Dark Emerald
        headerPanel.add(titleLabel, BorderLayout.CENTER);

        JButton backButton = UIStyles.createBackButton("< Voltar");
        backButton.setPreferredSize(new Dimension(100, 35));
        backButton.addActionListener(DebounceUtil.debounce(e -> {
            controller.detachSerial();

            // Desliga a porta para liberá-la antes de voltar pro scanner raw de hardware
            controller.getSerialService().disconnect();

            AuthenticationService authService = new AuthenticationService(apiConfig);
            ConnectionController connController = new ConnectionController(controller.getSerialService());
            ConnectionPanel connPanel = new ConnectionPanel(connController, authService, apiConfig, navManager);
            navManager.showPanel("Connection", connPanel);
        }, DebounceUtil.NAV_MS));
        headerPanel.add(backButton, BorderLayout.WEST);

        JButton logButton = new JButton("Ver Logs Serial");
        logButton.setFont(new Font("Arial", Font.PLAIN, 10));
        logButton.addActionListener(DebounceUtil.debounce(e -> {
            SerialLogFrame logFrame = new SerialLogFrame(controller.getSerialService());
            logFrame.setVisible(true);
        }, DebounceUtil.NAV_MS));
        headerPanel.add(logButton, BorderLayout.EAST);
        add(headerPanel, BorderLayout.NORTH);

        // Center Panel (Status e Leitura)
        JPanel centerPanel = new JPanel(new GridLayout(2, 1, 5, 20));
        centerPanel.setBorder(UIStyles.createCardBorder());
        centerPanel.setBackground(Color.WHITE);

        statusLabel = new JLabel("Aguardando conexão...", SwingConstants.CENTER);
        statusLabel.setFont(UIStyles.BODY_FONT);
        statusLabel.setForeground(UIStyles.TEXT_DARK);
        centerPanel.add(statusLabel);

        JPanel buttonPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        buttonPanel.setBackground(Color.WHITE);
        readCardButton = UIStyles.createPrimaryButton("Aproximar Crachá (READ)");
        readCardButton.setPreferredSize(new Dimension(250, 50));
        readCardButton.setEnabled(false);
        readCardButton.addActionListener(DebounceUtil.debounce(e -> controller.requestCardLogin()));
        buttonPanel.add(readCardButton);

        centerPanel.add(buttonPanel);

        // Wrapper for centering
        JPanel wrapperPanel = new JPanel(new GridBagLayout());
        wrapperPanel.setBackground(UIStyles.BACKGROUND);
        wrapperPanel.add(centerPanel);

        add(wrapperPanel, BorderLayout.CENTER);
    }

    // Callbacks do Controller
    @Override
    public void onLoginSuccess(User user) {
        SwingUtilities.invokeLater(() -> {
            JOptionPane.showMessageDialog(this, "Bem-vindo(a), " + user.getName() + "!", "Acesso Liberado", JOptionPane.INFORMATION_MESSAGE);

            // Sucesso! Esconde esta tela e abre a MainPanel
            controller.detachSerial();

            // Seta o state global
            if (navManager instanceof ApplicationFrame) {
                ((ApplicationFrame) navManager).setLoggedUser(user);
            }

            // Instancia o repositorio e controller global do sistema
            CattleApiService apiService = new CattleApiService(apiConfig, user);
            CattleController cattleController = new CattleController(apiService, controller.getSerialService());

            MainPanel mainPanel = new MainPanel(user, cattleController, navManager, apiConfig);
            navManager.showPanel("Main", mainPanel);
        });
    }

    @Override
    public void onLoginError(String message) {
        SwingUtilities.invokeLater(() -> {
            readCardButton.setEnabled(true);
            readCardButton.setText("Aproximar Crachá (READ)");
            statusLabel.setText(message);
            statusLabel.setForeground(Color.RED);
            JOptionPane.showMessageDialog(this, message, "Erro de Login", JOptionPane.ERROR_MESSAGE);
        });
    }

    @Override
    public void onSerialConnected() {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Arduino Conectado! Por favor, leia seu crachá.");
            statusLabel.setForeground(UIStyles.PRIMARY); // Premium Emerald
            readCardButton.setEnabled(true);
        });
    }

    @Override
    public void onSerialError(String message) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText(message);
            statusLabel.setForeground(Color.RED);
            JOptionPane.showMessageDialog(this, message, "Erro da Porta", JOptionPane.ERROR_MESSAGE);
        });
    }

    @Override
    public void onWaitingForCard() {
        SwingUtilities.invokeLater(() -> {
            readCardButton.setText("Lendo... Aproxime o cartão");
            statusLabel.setText("Aguardando leitura do RFID (Timeout " + (RfidConstants.SERIAL_READ_TIMEOUT_MS / 1000.0) + "s)...");
            statusLabel.setForeground(Color.BLUE);
        });
    }
}
