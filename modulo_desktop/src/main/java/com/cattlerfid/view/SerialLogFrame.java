package com.cattlerfid.view;

import com.cattlerfid.service.SerialService;
import com.cattlerfid.util.DebounceUtil;

import javax.swing.*;
import java.awt.*;
import java.util.List;
import java.util.function.Consumer;

public class SerialLogFrame extends JFrame {

    private final SerialService serialService;
    private JTextArea logArea;
    private final Consumer<String> logListener = this::onLogAppended;

    public SerialLogFrame(SerialService serialService) {
        this.serialService = serialService;

        setupUI();
        pack();
        setLocationRelativeTo(null);
        setDefaultCloseOperation(DISPOSE_ON_CLOSE);

        loadHistoryAndSubscribe();
    }

    private void setupUI() {
        setTitle("Log de Comunicação Serial (Arduino <-> Java)");
        setLayout(new BorderLayout(5, 5));
        setPreferredSize(new Dimension(500, 350));

        logArea = new JTextArea();
        logArea.setEditable(false);
        logArea.setFont(new Font("Consolas", Font.PLAIN, 12));
        logArea.setBackground(new Color(30, 30, 30));
        logArea.setForeground(new Color(0, 255, 0));

        JScrollPane scrollPane = new JScrollPane(logArea);
        add(scrollPane, BorderLayout.CENTER);

        JPanel actionPanel = new JPanel(new BorderLayout(5, 5));

        JPanel inputPanel = new JPanel(new BorderLayout(5, 0));
        JTextField inputField = new JTextField();
        JButton sendButton = new JButton("Enviar >");
        inputPanel.add(new JLabel(" Comando Direto: "), BorderLayout.WEST);
        inputPanel.add(inputField, BorderLayout.CENTER);
        inputPanel.add(sendButton, BorderLayout.EAST);

        sendButton.addActionListener(DebounceUtil.debounce(e -> {
            String text = inputField.getText();
            if (!text.isEmpty()) {
                serialService.sendCommand(text + "\n");
                inputField.setText("");
            }
        }));
        inputField.addActionListener(e -> sendButton.doClick());

        JPanel buttonsPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        JButton clearButton = new JButton("Limpar Tela");
        clearButton.addActionListener(e -> logArea.setText(""));

        JButton closeButton = new JButton("Fechar");
        closeButton.addActionListener(e -> dispose());

        buttonsPanel.add(clearButton);
        buttonsPanel.add(closeButton);

        actionPanel.add(inputPanel, BorderLayout.CENTER);

        if (serialService.isSimulationMode()) {
            JPanel simulationPanel = new JPanel(new GridLayout(2, 1, 5, 5));
            simulationPanel.setBorder(BorderFactory.createTitledBorder("Simulação de Hardware"));

            JPanel readSimPanel = new JPanel(new BorderLayout(5, 0));
            JTextField tagInputField = new JTextField("1234567890123456");
            JButton simReadButton = new JButton("Simular Leitura (IN)");
            readSimPanel.add(new JLabel(" Tag ID: "), BorderLayout.WEST);
            readSimPanel.add(tagInputField, BorderLayout.CENTER);
            readSimPanel.add(simReadButton, BorderLayout.EAST);

            simReadButton.addActionListener(DebounceUtil.debounce(e -> {
                String tag = tagInputField.getText().trim();
                if (!tag.isEmpty()) {
                    serialService.injectMessage("READ:OK:" + tag);
                }
            }));

            JPanel writeSimPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
            JButton simWriteOkButton = new JButton("Simular Sucesso Gravação");
            JButton simWriteErrButton = new JButton("Simular Erro Gravação");

            simWriteOkButton.addActionListener(DebounceUtil.debounce(e -> serialService.injectMessage("WRITE:OK")));
            simWriteErrButton.addActionListener(DebounceUtil.debounce(e -> serialService.injectMessage("WRITE:ERR:GRAVACAO_FALHOU")));

            writeSimPanel.add(simWriteOkButton);
            writeSimPanel.add(simWriteErrButton);

            simulationPanel.add(readSimPanel);
            simulationPanel.add(writeSimPanel);
            actionPanel.add(simulationPanel, BorderLayout.NORTH);
        }

        actionPanel.add(buttonsPanel, BorderLayout.SOUTH);

        add(actionPanel, BorderLayout.SOUTH);
    }

    private void loadHistoryAndSubscribe() {

        List<String> history = serialService.getLogHistory();
        for (String line : history) {
            logArea.append(line + "\n");
        }

        serialService.addLogListener(logListener);
    }

    private void onLogAppended(String newLine) {
        SwingUtilities.invokeLater(() -> {
            logArea.append(newLine + "\n");

            logArea.setCaretPosition(logArea.getDocument().getLength());
        });
    }

    @Override
    public void dispose() {

        serialService.removeLogListener(logListener);
        super.dispose();
    }
}
