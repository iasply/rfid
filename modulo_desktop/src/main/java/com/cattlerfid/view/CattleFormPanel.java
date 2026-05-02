package com.cattlerfid.view;

import com.cattlerfid.controller.CattleController;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.util.DateUtils;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;

public class CattleFormPanel extends JPanel {

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

    public CattleFormPanel(Cattle cattle, boolean isNew, boolean isManual,
            CattleController controller,
            User loggedUser, NavigationManager navManager, MainPanel parentMainPanel) {
        this.cattle = cattle;
        this.isNew = isNew;
        this.isManual = isManual;
        this.controller = controller;
        this.loggedUser = loggedUser;
        this.navManager = navManager;
        this.parentMainPanel = parentMainPanel;

        setupUI();
        populateFields();
    }

    private void setupUI() {
        setLayout(new BorderLayout(10, 10));
        setBackground(UIStyles.BACKGROUND);

        // Header Title
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.BACKGROUND);
        headerPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        JLabel titleLabel = UIStyles.createTitleLabel(
                isNew ? "Novo Cadastro de Animal" : "Editando Animal");
        headerPanel.add(titleLabel, BorderLayout.CENTER);

        JButton backButton = UIStyles.createBackButton("< Voltar");
        backButton.setPreferredSize(new Dimension(100, 30));
        backButton.addActionListener(e -> {
            // Aborta edição e volta
            if (parentMainPanel != null) {
                parentMainPanel.setActiveCattleForm(null); // Clear ref
            }
            navManager.showPanel("Main", parentMainPanel);
        });
        headerPanel.add(backButton, BorderLayout.WEST);
        add(headerPanel, BorderLayout.NORTH);

        // Form Container with Card Styling
        JPanel cardPanel = new JPanel(new GridBagLayout());
        cardPanel.setBackground(Color.WHITE);
        cardPanel.setBorder(UIStyles.createCardBorder());

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // RFID Tag
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

        // Responsável
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

        // Nome
        gbc.gridx = 0;
        gbc.gridy = 2;
        JLabel nameLabel = new JLabel("Nome / Apelido:");
        nameLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(nameLabel, gbc);

        gbc.gridx = 1;
        nameField = new JTextField();
        nameField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(nameField, gbc);

        // Peso
        gbc.gridx = 0;
        gbc.gridy = 3;
        JLabel weightLabel = new JLabel("Peso (kg):");
        weightLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(weightLabel, gbc);

        gbc.gridx = 1;
        weightField = new JTextField();
        weightField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(weightField, gbc);

        // Data
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

        // Wrapper for true centering
        JPanel wrapperPanel = new JPanel(new GridBagLayout());
        wrapperPanel.setBackground(UIStyles.BACKGROUND);
        wrapperPanel.add(cardPanel);

        add(wrapperPanel, BorderLayout.CENTER);

        // Botoes Pannel
        JPanel buttonPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 20, 10));
        buttonPanel.setBackground(UIStyles.BACKGROUND);

        writeTagButton = UIStyles.createPrimaryButton("1. Gravar Tag Física");
        writeTagButton.setPreferredSize(new Dimension(220, 40));
        writeTagButton.setBackground(UIStyles.WARNING); // Gold for Tag writing
        writeTagButton.setForeground(UIStyles.PRIMARY_DARK);
        writeTagButton.addActionListener(e -> writeTagAction());
        // Apenas habilita gravação física se for manual
        writeTagButton.setVisible(isManual);
        buttonPanel.add(writeTagButton);

        saveDbButton = UIStyles.createSuccessButton("2. Salvar no Banco");
        saveDbButton.setPreferredSize(new Dimension(220, 40));
        saveDbButton.setBackground(UIStyles.PRIMARY); // Emerald for Saving
        saveDbButton.addActionListener(e -> saveDbAction());
        saveDbButton.setEnabled(
                !isManual || !isNew); // Habilita direto se for edição ou se não for manual
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
                JOptionPane.showMessageDialog(this, "Peso deve estar entre 0 e 2000 kg.",
                        "Peso inválido", JOptionPane.ERROR_MESSAGE);
                return;
            }

            cattle.setName(nameField.getText().trim());
            cattle.setWeight(weight);

            if (isManual) {
                writeTagButton.setEnabled(false);
                writeTagButton.setText("Gravando na Tag...");
                controller.requestWriteTag(cattle.getRfidTag());
            }

        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Peso inválido. Use formato número decimal.",
                    "Erro de Digitação",
                    JOptionPane.ERROR_MESSAGE);
        }
    }

    private void saveDbAction() {
        try {
            double weight = 0.0;
            if (!weightField.getText().trim().isEmpty()) {
                weight = Double.parseDouble(weightField.getText().replace(",", "."));
            }
            if (weight < 0 || weight > 2000) {
                JOptionPane.showMessageDialog(this, "Peso deve estar entre 0 e 2000 kg.",
                        "Peso inválido", JOptionPane.ERROR_MESSAGE);
                return;
            }

            cattle.setName(nameField.getText().trim());
            cattle.setWeight(weight);

            // The dateField is not editable, so its value is already set during
            // populateFields
            // or initialization for new cattle. No need to re-read and format here for
            // cattle.setRegistrationDate.

            controller.saveCattleData(cattle);

        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Peso inválido. Use formato número decimal.",
                    "Erro de Digitação",
                    JOptionPane.ERROR_MESSAGE);
            saveDbButton.setEnabled(true);
        }
    }

    // Callback para quando a gravação física der erro
    public void resetSubmitButton() {
        writeTagButton.setEnabled(true);
        writeTagButton.setText("Tentar Gravar Tag Novamente");
    }

    // Callback para sucesso físico
    public void onTagWriteSuccess() {
        writeTagButton.setEnabled(false);
        writeTagButton.setText("Tag Gravada!");
        writeTagButton.setBackground(new Color(144, 238, 144)); // Verde claro

        saveDbButton.setEnabled(true);
        saveDbAction(); // Salva automaticamente ao dar sucesso

        // Transição de sucesso
        if (parentMainPanel != null) {
            parentMainPanel.setActiveCattleForm(null);
        }
        navManager.showPanel("Main", parentMainPanel);
    }

    // Getter para os dados montados
    public Cattle getPendingCattle() {
        return cattle;
    }
}
