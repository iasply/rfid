package com.cattlerfid.view;

import javax.swing.*;

public interface NavigationManager {

    /**
     * Navega para um novo painel, substituindo a visualização atual.
     *
     * @param name
     *         O nome/rota do painel (ex: "Login", "Home").
     * @param panel
     *         A instância do painel a ser renderizada.
     */
    void showPanel(String name, JPanel panel);
}
