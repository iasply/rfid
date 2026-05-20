package com.cattlerfid.view;

import com.cattlerfid.controller.CattleController;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.util.DateUtils;
import com.cattlerfid.util.DebounceUtil;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;

public class CattleFormPanel extends JPanel implements CattleController.CattleViewListener {

    private final Cattle cattle;
    private final boolean isNew;
    private final boolean isManual;
    private final CattleController controller;
    private final User loggedUser;

    private final NavigationManager navManager;
    private final MainPanel parentMainPanel;

    private JTextField nameField;
    private JTextField weightField;
    private JTextField dateField;
    private JButton writeTagButton;
    private JButton saveDbButton;

    public CattleFormPanel(Cattle cattle, boolean isNew, boolean isManual, CattleController controller, User loggedUser, NavigationManager navManager, MainPanel parentMainPanel) {
        this.cattle = cattle;
        this.isNew = isNew;
        this.isManual = isManual;
        this.controller = controller;
        this.loggedUser = loggedUser;
        this.navManager = navManager;
        this.parentMainPanel = parentMainPanel;

        controller.setViewListener(this);
        setupUI();
        populateFields();
    }

    private void setupUI() {
        setLayout(new BorderLayout(10, 10));
        setBackground(UIStyles.BACKGROUND);

        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.BACKGROUND);
        headerPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        JLabel titleLabel = UIStyles.createTitleLabel(isNew ? "Novo Cadastro de Animal" : "Editando Animal");
        headerPanel.add(titleLabel, BorderLayout.CENTER);

        JButton backButton = UIStyles.createBackButton("< Voltar");
        backButton.setPreferredSize(new Dimension(100, 30));
        backButton.addActionListener(DebounceUtil.debounce(e -> {
            navManager.showPanel("Main", parentMainPanel);
        }, DebounceUtil.NAV_MS));
        headerPanel.add(backButton, BorderLayout.WEST);
        add(headerPanel, BorderLayout.NORTH);

        JPanel cardPanel = new JPanel(new GridBagLayout());
        cardPanel.setBackground(Color.WHITE);
        cardPanel.setBorder(UIStyles.createCardBorder());

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        gbc.gridx = 0;
        gbc.gridy = 0;
        JLabel tagLabel = new JLabel("RFID Tag:");
        tagLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(tagLabel, gbc);

        gbc.gridx = 1;
        JTextField tagField = new JTextField(cattle.getRfidTag());
        tagField.setFont(UIStyles.BODY_FONT);
        tagField.setEditable(false);
        tagField.setBackground(UIStyles.SECONDARY);
        cardPanel.add(tagField, gbc);

        gbc.gridx = 0;
        gbc.gridy = 1;
        JLabel respLabel = new JLabel("Responsável:");
        respLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(respLabel, gbc);

        gbc.gridx = 1;
        JTextField userField = new JTextField(loggedUser.getName());
        userField.setFont(UIStyles.BODY_FONT);
        userField.setEditable(false);
        userField.setBackground(UIStyles.SECONDARY);
        cardPanel.add(userField, gbc);

        gbc.gridx = 0;
        gbc.gridy = 2;
        JLabel nameLabel = new JLabel("Nome / Apelido:");
        nameLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(nameLabel, gbc);

        gbc.gridx = 1;
        nameField = new JTextField();
        nameField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(nameField, gbc);

        gbc.gridx = 0;
        gbc.gridy = 3;
        JLabel weightLabel = new JLabel("Peso (kg):");
        weightLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(weightLabel, gbc);

        gbc.gridx = 1;
        weightField = new JTextField();
        weightField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(weightField, gbc);

        gbc.gridx = 0;
        gbc.gridy = 4;
        JLabel dateLabel = new JLabel("Data de Cadastro:");
        dateLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(dateLabel, gbc);

        gbc.gridx = 1;
        dateField = new JTextField();
        dateField.setFont(UIStyles.BODY_FONT);
        dateField.setEditable(false);
        dateField.setBackground(UIStyles.SECONDARY);
        cardPanel.add(dateField, gbc);

        JPanel wrapperPanel = new JPanel(new GridBagLayout());
        wrapperPanel.setBackground(UIStyles.BACKGROUND);
        wrapperPanel.add(cardPanel);

        add(wrapperPanel, BorderLayout.CENTER);

        JPanel buttonPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 20, 10));
        buttonPanel.setBackground(UIStyles.BACKGROUND);

        writeTagButton = UIStyles.createPrimaryButton("1. Gravar Tag Física");
        writeTagButton.setPreferredSize(new Dimension(220, 40));
        writeTagButton.setBackground(UIStyles.WARNING);
        writeTagButton.setForeground(UIStyles.PRIMARY_DARK);
        writeTagButton.addActionListener(DebounceUtil.debounce(e -> writeTagAction()));

        writeTagButton.setVisible(isManual);
        buttonPanel.add(writeTagButton);

        saveDbButton = UIStyles.createSuccessButton("2. Salvar no Banco");
        saveDbButton.setPreferredSize(new Dimension(220, 40));
        saveDbButton.setBackground(UIStyles.PRIMARY);
        saveDbButton.addActionListener(DebounceUtil.debounce(e -> saveDbAction()));
        saveDbButton.setEnabled(!isManual || !isNew);
        buttonPanel.add(saveDbButton);

        add(buttonPanel, BorderLayout.SOUTH);
    }

    private void populateFields() {
        if (!isNew) {
            nameField.setText(cattle.getName() != null ? cattle.getName() : "");
            weightField.setText(cattle.getWeight() > 0 ? String.valueOf(cattle.getWeight()) : "");

            String dateStr = cattle.getRegistrationDate();
            dateField.setText(DateUtils.toDisplayDate(dateStr));
        } else {
            String today = java.time.LocalDate.now().toString();
            dateField.setText(DateUtils.toDisplayDate(today));
            cattle.setRegistrationDate(today);
        }
    }

    private void writeTagAction() {
        try {
            double weight = 0.0;
            if (!weightField.getText().trim().isEmpty()) {
                weight = Double.parseDouble(weightField.getText().replace(",", "."));
            }
            if (weight < 0 || weight > 2000) {
                JOptionPane.showMessageDialog(this, "Peso deve estar entre 0 e 2000 kg.", "Peso inválido", JOptionPane.ERROR_MESSAGE);
                return;
            }

            cattle.setName(nameField.getText().trim());
            cattle.setWeight(weight);

            writeTagButton.setEnabled(false);
            writeTagButton.setText("Gravando na Tag...");
            controller.requestWriteTag(cattle.getRfidTag());

        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Peso inválido. Use formato número decimal.", "Erro de Digitação", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void saveDbAction() {
        try {
            double weight = 0.0;
            if (!weightField.getText().trim().isEmpty()) {
                weight = Double.parseDouble(weightField.getText().replace(",", "."));
            }
            if (weight < 0 || weight > 2000) {
                JOptionPane.showMessageDialog(this, "Peso deve estar entre 0 e 2000 kg.", "Peso inválido", JOptionPane.ERROR_MESSAGE);
                return;
            }

            cattle.setName(nameField.getText().trim());
            cattle.setWeight(weight);

            controller.saveCattleData(cattle);

        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Peso inválido. Use formato número decimal.", "Erro de Digitação", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void navigateBack() {
        controller.setViewListener(parentMainPanel);
        navManager.showPanel("Main", parentMainPanel);
    }

    @Override
    public void onRfidReadSuccess(Cattle cattle) {}

    @Override
    public void onRfidReadError(String message) {
        SwingUtilities.invokeLater(() -> JOptionPane.showMessageDialog(this, message, "Aviso RFID", JOptionPane.WARNING_MESSAGE));
    }

    @Override
    public void onRfidWriteSuccess() {
        SwingUtilities.invokeLater(() -> {
            writeTagButton.setEnabled(false);
            writeTagButton.setText("Tag Gravada!");
            writeTagButton.setBackground(new Color(144, 238, 144));
            saveDbButton.setEnabled(true);
            saveDbAction();
        });
    }

    @Override
    public void onRfidWriteError(String message) {
        SwingUtilities.invokeLater(() -> {
            writeTagButton.setEnabled(true);
            writeTagButton.setText("Tentar Gravar Tag Novamente");
            JOptionPane.showMessageDialog(this, message + "\nPosicione a TAG sob o leitor corretamente e tente novamente.", "Erro ao Gravar RFID", JOptionPane.ERROR_MESSAGE);
        });
    }

    @Override
    public void onApiSaveSuccess() {
        SwingUtilities.invokeLater(() -> {
            JOptionPane.showMessageDialog(this, "Registro concluído e salvo no servidor!", "Sucesso", JOptionPane.INFORMATION_MESSAGE);
            navigateBack();
        });
    }

    @Override
    public void onApiSaveError(String message) {
        SwingUtilities.invokeLater(() -> JOptionPane.showMessageDialog(this, message, "Erro Base de Dados", JOptionPane.ERROR_MESSAGE));
    }
}
