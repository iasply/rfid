package com.cattlerfid.view;

import com.cattlerfid.model.User;

import javax.swing.*;
import java.awt.*;

public class ApplicationFrame extends JFrame implements NavigationManager {

    private final JPanel containerPanel;
    private final CardLayout cardLayout;

    private User loggedUser;

    public ApplicationFrame() {
        setTitle("Sistema de Vacinação - Cattle RFID");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setPreferredSize(new Dimension(800, 600));

        cardLayout = new CardLayout();
        containerPanel = new JPanel(cardLayout);

        add(containerPanel, BorderLayout.CENTER);
        pack();
        setLocationRelativeTo(null);
    }

    @Override
    public void showPanel(String name, JPanel panel) {
        containerPanel.add(panel, name);
        cardLayout.show(containerPanel, name);
    }

    public User getLoggedUser() {
        return loggedUser;
    }

    public void setLoggedUser(User user) {
        this.loggedUser = user;
    }
}
