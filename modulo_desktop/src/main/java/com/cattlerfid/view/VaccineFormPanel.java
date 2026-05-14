package com.cattlerfid.view;

import com.cattlerfid.controller.CattleController;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.model.Vaccine;
import com.cattlerfid.model.VaccineType;
import com.cattlerfid.util.DateUtils;
import com.cattlerfid.util.DebounceUtil;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import java.awt.*;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.List;

public class VaccineFormPanel extends JPanel {

    private final Cattle cattle;
    private final CattleController controller;
    private final User loggedUser;
    private final NavigationManager navManager;
    private final MainPanel parentMainPanel;

    private JTextField dateField;
    private JComboBox<VaccineType> vaccineTypeCombo;
    private JTextField weightField;
    private JButton submitButton;

    public VaccineFormPanel(Cattle cattle, CattleController controller, User loggedUser,
            NavigationManager navManager, MainPanel parentMainPanel,
            List<VaccineType> vaccineTypes) {
        this.cattle = cattle;
        this.controller = controller;
        this.loggedUser = loggedUser;
        this.navManager = navManager;
        this.parentMainPanel = parentMainPanel;

        setupUI(vaccineTypes);
    }

    private void setupUI(List<VaccineType> vaccineTypes) {
        setLayout(new BorderLayout(10, 10));
        setBackground(UIStyles.BACKGROUND);

        // Header
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(UIStyles.BACKGROUND);
        headerPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        JLabel titleLabel = UIStyles.createTitleLabel(
                "Registro de Vacinação - " + cattle.getRfidTag());
        headerPanel.add(titleLabel, BorderLayout.CENTER);

        JButton backButton = UIStyles.createBackButton("< Voltar");
        backButton.setPreferredSize(new Dimension(100, 30));
        backButton.addActionListener(DebounceUtil.debounce(e -> navManager.showPanel("Main", parentMainPanel), DebounceUtil.NAV_MS));
        headerPanel.add(backButton, BorderLayout.WEST);
        add(headerPanel, BorderLayout.NORTH);

        // Form card
        JPanel cardPanel = new JPanel(new GridBagLayout());
        cardPanel.setBackground(Color.WHITE);
        cardPanel.setBorder(UIStyles.createCardBorder());

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Tag RFID
        addRow(cardPanel, gbc, 0, "Tag RFID:", makeReadonlyField(cattle.getRfidTag()));

        // Nome
        addRow(cardPanel, gbc, 1, "Nome do Animal:",
                makeReadonlyField(cattle.getName() != null ? cattle.getName() : ""));

        // Veterinário
        addRow(cardPanel, gbc, 2, "Veterinário:", makeReadonlyField(loggedUser.getName()));

        // Tipo da Vacina (dropdown)
        gbc.gridx = 0;
        gbc.gridy = 3;
        JLabel vLabel = new JLabel("Tipo da Vacina:");
        vLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(vLabel, gbc);

        gbc.gridx = 1;
        DefaultComboBoxModel<VaccineType> comboModel = new DefaultComboBoxModel<>();
        if (vaccineTypes == null || vaccineTypes.isEmpty()) {
            comboModel.addElement(null);  // fallback — won't happen if server is reachable
        } else {
            for (VaccineType vt : vaccineTypes) {
                comboModel.addElement(vt);
            }
        }
        vaccineTypeCombo = new JComboBox<>(comboModel);
        vaccineTypeCombo.setFont(UIStyles.BODY_FONT);
        cardPanel.add(vaccineTypeCombo, gbc);

        // Peso
        gbc.gridx = 0;
        gbc.gridy = 4;
        JLabel wLabel = new JLabel("Peso Atual (kg):");
        wLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(wLabel, gbc);

        gbc.gridx = 1;
        weightField = new JTextField(
                cattle.getWeight() > 0 ? String.valueOf(cattle.getWeight()) : "");
        weightField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(weightField, gbc);

        // Data
        gbc.gridx = 0;
        gbc.gridy = 5;
        JLabel dLabel = new JLabel("Data Aplicação:");
        dLabel.setFont(UIStyles.LABEL_FONT);
        cardPanel.add(dLabel, gbc);

        gbc.gridx = 1;
        dateField = new JTextField(
                LocalDate.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy")));
        dateField.setFont(UIStyles.BODY_FONT);
        cardPanel.add(dateField, gbc);

        JPanel wrapperPanel = new JPanel(new GridBagLayout());
        wrapperPanel.setBackground(UIStyles.BACKGROUND);
        wrapperPanel.add(cardPanel);
        add(wrapperPanel, BorderLayout.CENTER);

        // Buttons
        JPanel buttonPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 20, 10));
        buttonPanel.setBackground(UIStyles.BACKGROUND);

        submitButton = UIStyles.createSuccessButton("Registrar Vacina");
        submitButton.setPreferredSize(new Dimension(250, 45));
        submitButton.setBackground(UIStyles.PRIMARY);
        submitButton.addActionListener(DebounceUtil.debounce(e -> saveAction()));
        buttonPanel.add(submitButton);

        add(buttonPanel, BorderLayout.SOUTH);
    }

    private JTextField makeReadonlyField(String text) {
        JTextField f = new JTextField(text);
        f.setFont(UIStyles.BODY_FONT);
        f.setEditable(false);
        f.setBackground(UIStyles.SECONDARY);
        return f;
    }

    private void addRow(JPanel panel, GridBagConstraints gbc, int row, String labelText,
            JComponent field) {
        gbc.gridx = 0;
        gbc.gridy = row;
        JLabel label = new JLabel(labelText);
        label.setFont(UIStyles.LABEL_FONT);
        panel.add(label, gbc);
        gbc.gridx = 1;
        panel.add(field, gbc);
    }

    private void saveAction() {
        try {
            VaccineType selectedType = (VaccineType) vaccineTypeCombo.getSelectedItem();
            if (selectedType == null) {
                JOptionPane.showMessageDialog(this, "Selecione um tipo de vacina.", "Aviso",
                        JOptionPane.WARNING_MESSAGE);
                return;
            }

            double weight = 0.0;
            if (!weightField.getText().trim().isEmpty()) {
                weight = Double.parseDouble(weightField.getText().replace(",", "."));
            }
            if (weight < 0 || weight > 2000) {
                JOptionPane.showMessageDialog(this, "Peso deve estar entre 0 e 2000 kg.",
                        "Peso inválido", JOptionPane.ERROR_MESSAGE);
                return;
            }

            LocalDate date = LocalDate.parse(dateField.getText().trim(),
                    DateTimeFormatter.ofPattern("dd/MM/yyyy"));

            Vaccine v = new Vaccine();
            v.setRfidTag(cattle.getRfidTag());
            v.setVaccineTypeId(selectedType.getId());
            v.setCurrentWeight(weight);
            v.setVaccinationDate(DateUtils.toIsoDate(dateField.getText().trim()));

            controller.saveVaccineData(v, cattle, weight);
            navManager.showPanel("Main", parentMainPanel);

        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Peso inválido. Use formato número decimal.",
                    "Erro",
                    JOptionPane.ERROR_MESSAGE);
        } catch (DateTimeParseException ex) {
            JOptionPane.showMessageDialog(this, "Data inválida. Use formato DD/MM/AAAA.", "Erro",
                    JOptionPane.ERROR_MESSAGE);
        }
    }
}
