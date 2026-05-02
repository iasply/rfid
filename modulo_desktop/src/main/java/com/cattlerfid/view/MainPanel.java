package com.cattlerfid.view;

import com.cattlerfid.config.ApiConfig;
import com.cattlerfid.controller.CattleController;
import com.cattlerfid.controller.LoginController;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.model.VaccineType;
import com.cattlerfid.service.AuthenticationService;
import com.cattlerfid.util.RfidGenerator;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;
import java.util.ArrayList;
import java.util.List;

public class MainPanel extends JPanel implements CattleController.CattleViewListener {

    private final User loggedUser;
    private final CattleController cattleController;
    private final NavigationManager navManager;
    private final ApiConfig apiConfig;

    private JLabel statusLabel;
    private JButton scanCattleButton;

    private CattleFormPanel activeCattleForm;
    private List<VaccineType> vaccineTypes = new ArrayList<>();

    public MainPanel(User loggedUser, CattleController cattleController,
            NavigationManager navManager, ApiConfig apiConfig) {
        this.loggedUser = loggedUser;
        this.cattleController = cattleController;
        this.navManager = navManager;
        this.apiConfig = apiConfig;
        this.cattleController.setViewListener(this);

        setupUI();
        loadVaccineTypesAsync();
    }

    private void loadVaccineTypesAsync() {
        new SwingWorker<List<VaccineType>, Void>() {
            protected List<VaccineType> doInBackground() {
                return cattleController.getApiService().getVaccineTypes();
            }

            protected void done() {
                try {
                    vaccineTypes = get();
                } catch (Exception ex) {
                    System.err.println("[MainPanel] Failed to load vaccine types: " + ex.getMessage());
                }
            }
        }.execute();
    }

    private void setupUI() {
        setLayout(new BorderLayout(10, 10));
        setBackground(UIStyles.BACKGROUND);

        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.PRIMARY_DARK);
        headerPanel.setBorder(BorderFactory.createEmptyBorder(15, 20, 15, 20));

        JLabel welcomeLabel = new JLabel("Usuário: " + loggedUser.getName() + " (Veterinário)");
        welcomeLabel.setForeground(UIStyles.TEXT_LIGHT);
        welcomeLabel.setFont(UIStyles.SUBHEADER_FONT);
        headerPanel.add(welcomeLabel, BorderLayout.WEST);

        JButton logoutButton = UIStyles.createBackButton("Sair (Logout)");
        logoutButton.addActionListener(e -> {
            int confirm = JOptionPane.showConfirmDialog(this,
                    "Tem certeza que deseja deslogar do sistema?", "Logout",
                    JOptionPane.YES_NO_OPTION);
            if (confirm == JOptionPane.YES_OPTION) {
                cattleController.detachSerial();

                AuthenticationService authService = new AuthenticationService(apiConfig);
                authService.logout(loggedUser.getAccessToken());

                LoginController loginController = new LoginController(authService,
                        cattleController.getSerialService());
                LoginPanel loginPanel = new LoginPanel(loginController, apiConfig, navManager);
                navManager.showPanel("Login", loginPanel);
                loginController.attachToActiveSerial();
            }
        });
        headerPanel.add(logoutButton, BorderLayout.EAST);

        add(headerPanel, BorderLayout.NORTH);

        JPanel centerPanel = new JPanel(new FlowLayout(FlowLayout.CENTER, 40, 80));
        centerPanel.setBackground(UIStyles.BACKGROUND);

        scanCattleButton = UIStyles.createPrimaryButton(
                "<html><center>IDENTIFICAR<br>E VACINAR</center></html>");
        scanCattleButton.setPreferredSize(new Dimension(220, 120));
        scanCattleButton.setFont(UIStyles.HEADER_FONT);
        scanCattleButton.setBackground(UIStyles.WARNING);
        scanCattleButton.setForeground(UIStyles.PRIMARY_DARK);
        scanCattleButton.addActionListener(e -> {
            statusLabel.setText("Aproxime a Tag do Animal...");
            cattleController.requestReadTag();
        });

        JButton manualRegisterButton = UIStyles
                .createPrimaryButton("<html><center>CADASTRAR<br>MANUAL</center></html>");
        manualRegisterButton.setPreferredSize(new Dimension(220, 120));
        manualRegisterButton.setFont(UIStyles.HEADER_FONT);
        manualRegisterButton.setBackground(UIStyles.PRIMARY);
        manualRegisterButton.addActionListener(e -> {
            statusLabel.setText("Preparando formulário manual...");

            String generatedTag = RfidGenerator.generateCattleTag();
            Cattle newCattle = new Cattle();
            newCattle.setRfidTag(generatedTag);

            activeCattleForm = new CattleFormPanel(newCattle, true, true, cattleController,
                    loggedUser, navManager, this);
            navManager.showPanel("ManualRegister", activeCattleForm);
        });

        JButton listButton = UIStyles.createPrimaryButton(
                "<html><center>LISTAR<br>REBANHO</center></html>");
        listButton.setPreferredSize(new Dimension(220, 120));
        listButton.setFont(UIStyles.HEADER_FONT);
        listButton.setBackground(UIStyles.SECONDARY);
        listButton.addActionListener(e -> {
            CattleListPanel listPanel = new CattleListPanel(cattleController.getApiService(),
                    cattleController, loggedUser, navManager, this);
            navManager.showPanel("List", listPanel);
        });

        centerPanel.add(scanCattleButton);
        centerPanel.add(manualRegisterButton);
        centerPanel.add(listButton);
        add(centerPanel, BorderLayout.CENTER);

        JPanel bottomPanel = new JPanel(new BorderLayout());
        bottomPanel.setBackground(UIStyles.SECONDARY);
        bottomPanel.setBorder(BorderFactory.createEmptyBorder(5, 10, 5, 10));

        statusLabel = new JLabel(" Sistema Pronto.", SwingConstants.LEFT);
        statusLabel.setFont(UIStyles.BODY_FONT);

        JButton logButton = new JButton("Ver Logs Serial");
        logButton.setFont(new Font("Arial", Font.PLAIN, 10));
        logButton.addActionListener(e -> {
            SerialLogFrame logFrame = new SerialLogFrame(cattleController.getSerialService());
            logFrame.setVisible(true);
        });

        bottomPanel.add(statusLabel, BorderLayout.CENTER);
        bottomPanel.add(logButton, BorderLayout.EAST);
        add(bottomPanel, BorderLayout.SOUTH);
    }

    @Override
    public void onRfidReadSuccess(Cattle cattle, boolean isNew) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Animal Encontrado (" + cattle.getRfidTag() + ")");
            VaccineFormPanel form = new VaccineFormPanel(cattle, cattleController, loggedUser,
                    navManager, this, vaccineTypes);
            navManager.showPanel("Vaccine", form);
        });
    }

    @Override
    public void onRfidReadError(String message) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Erro de Leitura: " + message);
            JOptionPane.showMessageDialog(this, message, "Aviso RFID", JOptionPane.WARNING_MESSAGE);
        });
    }

    @Override
    public void onRfidWriteSuccess() {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Tag gravada com sucesso!");
            if (activeCattleForm != null && activeCattleForm.isVisible()) {
                activeCattleForm.onTagWriteSuccess();
            }
        });
    }

    @Override
    public void onRfidWriteError(String message) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Erro de Escrita: " + message);
            JOptionPane.showMessageDialog(this,
                    message + "\nPosicione a TAG sob o leitor corretamente e tente novamente.",
                    "Erro ao Gravar RFID",
                    JOptionPane.ERROR_MESSAGE);
            if (activeCattleForm != null) {
                activeCattleForm.resetSubmitButton();
            }
        });
    }

    @Override
    public void onApiSaveSuccess() {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Dados salvos com sucesso.");
            JOptionPane.showMessageDialog(this, "Registro concluído e salvo no servidor!",
                    "Sucesso",
                    JOptionPane.INFORMATION_MESSAGE);
            activeCattleForm = null;
        });
    }

    @Override
    public void onApiSaveError(String message) {
        SwingUtilities.invokeLater(() -> {
            statusLabel.setText("Falha no Banco: " + message);
            JOptionPane.showMessageDialog(this, message, "Erro Base de Dados",
                    JOptionPane.ERROR_MESSAGE);
            if (activeCattleForm != null) {
                activeCattleForm.resetSubmitButton();
            }
        });
    }

    public void setActiveCattleForm(CattleFormPanel activeCattleForm) {
        this.activeCattleForm = activeCattleForm;
    }
}
