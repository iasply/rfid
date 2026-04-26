package com.cattlerfid.view;

import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.User;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Optional;

public class CattleListPanel extends JPanel {

    private final CattleApiService apiService;
    private final com.cattlerfid.controller.CattleController controller;
    private final User loggedUser;
    private final NavigationManager navManager;
    private final MainPanel parentMainPanel;

    private DefaultTableModel tableModel;
    private JTable table;

    public CattleListPanel(CattleApiService apiService,
            com.cattlerfid.controller.CattleController controller,
            User loggedUser, NavigationManager navManager, MainPanel parentMainPanel) {
        this.apiService = apiService;
        this.controller = controller;
        this.loggedUser = loggedUser;
        this.navManager = navManager;
        this.parentMainPanel = parentMainPanel;

        setupUI();
    }

    private void setupUI() {
        setLayout(new BorderLayout(15, 15));
        setBackground(UIStyles.BACKGROUND);

        JPanel topPanel = new JPanel(new BorderLayout());
        topPanel.setBackground(UIStyles.BACKGROUND);
        topPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        JLabel titleLabel = UIStyles.createTitleLabel("Listagem do Rebanho");
        topPanel.add(titleLabel, BorderLayout.WEST);

        JLabel descLabel = new JLabel("Visualizando registros da base de dados remota.");
        descLabel.setFont(UIStyles.BODY_FONT);
        topPanel.add(descLabel, BorderLayout.SOUTH);

        add(topPanel, BorderLayout.NORTH);

        // Configuração da Tabela
        String[] columnNames = {"Tag RFID", "Nome/Apelido", "Peso (kg)", "Data Registro", "Vacinas Aplicadas"};
        tableModel = new DefaultTableModel(columnNames, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false; // Apenas leitura
            }
        };

        table = new JTable(tableModel);
        table.setFillsViewportHeight(true);
        table.setRowHeight(35);
        table.getSelectionModel().setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.setFont(UIStyles.BODY_FONT);
        table.getTableHeader().setFont(UIStyles.SUBHEADER_FONT);
        table.getTableHeader().setBackground(UIStyles.PRIMARY_DARK); // Dark Emerald Header
        table.getTableHeader().setForeground(Color.WHITE);

        // Alternating row colors
        table.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {
            @Override
            public Component getTableCellRendererComponent(JTable table, Object value,
                    boolean isSelected,
                    boolean hasFocus, int row, int column) {
                Component c = super.getTableCellRendererComponent(table, value, isSelected,
                        hasFocus, row, column);
                if (!isSelected) {
                    c.setBackground(row % 2 == 0 ? Color.WHITE : UIStyles.BACKGROUND);
                }
                return c;
            }
        });

        refreshTable();

        JScrollPane scrollPane = new JScrollPane(table);
        scrollPane.setBorder(BorderFactory.createLineBorder(UIStyles.SECONDARY));

        JPanel centerWrapper = new JPanel(new BorderLayout());
        centerWrapper.setBackground(UIStyles.BACKGROUND);
        centerWrapper.setBorder(BorderFactory.createEmptyBorder(0, 15, 0, 15));
        centerWrapper.add(scrollPane, BorderLayout.CENTER);

        add(centerWrapper, BorderLayout.CENTER);

        // Botoes extra
        JPanel bottomPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 15, 10));
        bottomPanel.setBackground(UIStyles.BACKGROUND);

        JButton logButton = new JButton("Logs Serial");
        logButton.setFont(new Font("Arial", Font.PLAIN, 10));
        logButton.addActionListener(e -> {
            SerialLogFrame logFrame = new SerialLogFrame(controller.getSerialService());
            logFrame.setVisible(true);
        });
        bottomPanel.add(logButton);

        JButton editButton = UIStyles.createSuccessButton("Editar Selecionado");
        editButton.setPreferredSize(new Dimension(200, 35));
        editButton.addActionListener(e -> openEditDialog());
        bottomPanel.add(editButton);

        JButton closeBtn = UIStyles.createBackButton("< Menu");
        closeBtn.setPreferredSize(new Dimension(100, 35));
        closeBtn.addActionListener(e -> {
            navManager.showPanel("Main", parentMainPanel);
        });
        bottomPanel.add(closeBtn);
        add(bottomPanel, BorderLayout.SOUTH);
    }

    private void refreshTable() {
        tableModel.setRowCount(0); // Limpa tabela
        List<Cattle> allCattle = apiService.getAllCattleWithVaccines();
        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd/MM/yyyy");

        for (Cattle c : allCattle) {
            String dateStr = c.getRegistrationDate() != null ? c.getRegistrationDate() : "N/A";

            // Re-format YYYY-MM-DD to DD/MM/YYYY for UI
            if (dateStr.length() == 10 && dateStr.contains("-")) {
                String[] parts = dateStr.split("-");
                dateStr = parts[2] + "/" + parts[1] + "/" + parts[0];
            }

            int countVaccines = c.getVaccinesCount();

            Object[] row = {
                    c.getRfidTag(),
                    c.getName(),
                    c.getWeight(),
                    dateStr,
                    countVaccines
            };
            tableModel.addRow(row);
        }
    }

    private void openEditDialog() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow < 0) {
            JOptionPane.showMessageDialog(this, "Selecione um animal na lista para editar.",
                    "Nenhum animal selecionado", JOptionPane.WARNING_MESSAGE);
            return;
        }

        String tagId = (String) tableModel.getValueAt(selectedRow, 0);
        Optional<Cattle> targetOpt = apiService.getCattleByTag(tagId);

        if (targetOpt.isPresent()) {
            Cattle target = targetOpt.get();
            // Abre o formulario como isNew=false, isManual=true (permitir gravação RFID e
            // salvar no DB)
            CattleFormPanel form = new CattleFormPanel(target, false, true, controller, loggedUser,
                    navManager,
                    parentMainPanel);

            // Informa ao MainPanel (ouvinte master da porta serial) que esta é a tela
            // aguardando a gravação
            if (parentMainPanel != null) {
                parentMainPanel.setActiveCattleForm(form);
            }

            // Transitamos para o form de edição. Quando voltar ele vai passar por um novo
            // state,
            // mas nós podemos forçar o refresh ao chamar a lista novamente caso seja
            // necessário.
            navManager.showPanel("EditCattle", form);
        } else {
            JOptionPane.showMessageDialog(this, "Erro: Animal não encontrado na base de dados.",
                    "Erro",
                    JOptionPane.ERROR_MESSAGE);
        }
    }
}
