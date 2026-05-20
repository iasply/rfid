package com.cattlerfid.view;

import com.cattlerfid.controller.CattleController;
import com.cattlerfid.model.Cattle;
import com.cattlerfid.model.PagedResult;
import com.cattlerfid.model.User;
import com.cattlerfid.service.CattleApiService;
import com.cattlerfid.util.DateUtils;
import com.cattlerfid.util.DebounceUtil;
import com.cattlerfid.view.utils.UIStyles;

import javax.swing.*;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.util.Optional;

public class CattleListPanel extends JPanel {

    private final CattleApiService apiService;
    private final CattleController controller;
    private final User loggedUser;
    private final NavigationManager navManager;
    private final MainPanel parentMainPanel;

    private DefaultTableModel tableModel;
    private JTable table;

    private int currentPage = 1;
    private int lastPage = 1;
    private int totalRecords = 0;

    private JButton prevButton;
    private JButton nextButton;
    private JLabel pageLabel;
    private JButton editButton;

    public CattleListPanel(CattleApiService apiService, CattleController controller, User loggedUser, NavigationManager navManager, MainPanel parentMainPanel) {
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

        String[] columnNames = { "Tag RFID", "Nome/Apelido", "Peso (kg)", "Data Registro", "Vacinas Aplicadas" };
        tableModel = new DefaultTableModel(columnNames, 0) {

            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };

        table = new JTable(tableModel);
        table.setFillsViewportHeight(true);
        table.setRowHeight(35);
        table.getSelectionModel().setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.setFont(UIStyles.BODY_FONT);
        table.getTableHeader().setFont(UIStyles.SUBHEADER_FONT);
        table.getTableHeader().setBackground(UIStyles.PRIMARY_DARK);
        table.getTableHeader().setForeground(Color.WHITE);

        table.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {

            @Override
            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = super.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);
                if (!isSelected) {
                    c.setBackground(row % 2 == 0 ? Color.WHITE : UIStyles.BACKGROUND);
                }
                return c;
            }
        });

        JScrollPane scrollPane = new JScrollPane(table);
        scrollPane.setBorder(BorderFactory.createLineBorder(UIStyles.SECONDARY));

        JPanel centerWrapper = new JPanel(new BorderLayout());
        centerWrapper.setBackground(UIStyles.BACKGROUND);
        centerWrapper.setBorder(BorderFactory.createEmptyBorder(0, 15, 0, 15));
        centerWrapper.add(scrollPane, BorderLayout.CENTER);

        add(centerWrapper, BorderLayout.CENTER);

        JPanel southContainer = new JPanel(new BorderLayout());
        southContainer.setBackground(UIStyles.BACKGROUND);

        JPanel paginationPanel = new JPanel(new FlowLayout(FlowLayout.CENTER, 10, 8));
        paginationPanel.setBackground(UIStyles.BACKGROUND);

        prevButton = new JButton("< Anterior");
        prevButton.setFont(UIStyles.BODY_FONT);
        prevButton.setEnabled(false);
        prevButton.addActionListener(DebounceUtil.debounce(e -> loadPage(currentPage - 1)));

        pageLabel = new JLabel("Carregando…");
        pageLabel.setFont(UIStyles.BODY_FONT);
        pageLabel.setForeground(UIStyles.TEXT_MUTED);

        nextButton = new JButton("Próxima >");
        nextButton.setFont(UIStyles.BODY_FONT);
        nextButton.setEnabled(false);
        nextButton.addActionListener(DebounceUtil.debounce(e -> loadPage(currentPage + 1)));

        paginationPanel.add(prevButton);
        paginationPanel.add(pageLabel);
        paginationPanel.add(nextButton);

        southContainer.add(paginationPanel, BorderLayout.NORTH);

        JPanel bottomPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 15, 10));
        bottomPanel.setBackground(UIStyles.BACKGROUND);

        JButton logButton = new JButton("Logs Serial");
        logButton.setFont(new Font("Arial", Font.PLAIN, 10));
        logButton.addActionListener(DebounceUtil.debounce(e -> {
            SerialLogFrame logFrame = new SerialLogFrame(controller.getSerialService());
            logFrame.setVisible(true);
        }, DebounceUtil.NAV_MS));
        bottomPanel.add(logButton);

        editButton = UIStyles.createSuccessButton("Editar Selecionado");
        editButton.setPreferredSize(new Dimension(200, 35));
        editButton.addActionListener(DebounceUtil.debounce(e -> openEditDialog()));
        bottomPanel.add(editButton);

        JButton closeBtn = UIStyles.createBackButton("< Menu");
        closeBtn.setPreferredSize(new Dimension(100, 35));
        closeBtn.addActionListener(DebounceUtil.debounce(e -> navManager.showPanel("Main", parentMainPanel), DebounceUtil.NAV_MS));
        bottomPanel.add(closeBtn);

        southContainer.add(bottomPanel, BorderLayout.SOUTH);

        add(southContainer, BorderLayout.SOUTH);

        loadPage(1);
    }

    private void loadPage(int page) {
        prevButton.setEnabled(false);
        nextButton.setEnabled(false);
        editButton.setEnabled(false);
        pageLabel.setText("Carregando…");

        new SwingWorker<PagedResult<Cattle>, Void>() {

            @Override
            protected PagedResult<Cattle> doInBackground() {
                return apiService.getCattleWithVaccinesPaginated(page);
            }

            @Override
            protected void done() {
                try {
                    PagedResult<Cattle> result = get();
                    currentPage = result.getCurrentPage();
                    lastPage = result.getLastPage();
                    totalRecords = result.getTotal();

                    tableModel.setRowCount(0);
                    for (Cattle c : result.getData()) {
                        String dateStr = c.getRegistrationDate() != null ? DateUtils.toDisplayDate(c.getRegistrationDate()) : "N/A";
                        tableModel.addRow(new Object[] { c.getRfidTag(), c.getName(), c.getWeight(), dateStr, c.getVaccinesCount() });
                    }

                    updatePaginationControls();
                    editButton.setEnabled(true);
                } catch (Exception ex) {
                    pageLabel.setText("Erro ao carregar dados");
                    editButton.setEnabled(true);
                }
            }
        }.execute();
    }

    private void updatePaginationControls() {
        prevButton.setEnabled(currentPage > 1);
        nextButton.setEnabled(currentPage < lastPage);
        if (totalRecords == 0) {
            pageLabel.setText("Nenhum registro encontrado");
        } else {
            pageLabel.setText("Página " + currentPage + " de " + lastPage + "  (" + totalRecords + " registros)");
        }
    }

    private void openEditDialog() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow < 0) {
            JOptionPane.showMessageDialog(this, "Selecione um animal na lista para editar.", "Nenhum animal selecionado", JOptionPane.WARNING_MESSAGE);
            return;
        }

        String tagId = (String) tableModel.getValueAt(selectedRow, 0);
        Optional<Cattle> targetOpt = apiService.getCattleByTag(tagId);

        if (targetOpt.isPresent()) {
            Cattle target = targetOpt.get();
            CattleFormPanel form = new CattleFormPanel(target, false, true, controller, loggedUser, navManager, parentMainPanel);
            navManager.showPanel("EditCattle", form);
        } else {
            JOptionPane.showMessageDialog(this, "Erro: Animal não encontrado na base de dados.", "Erro", JOptionPane.ERROR_MESSAGE);
        }
    }
}
