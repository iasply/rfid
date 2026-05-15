package com.cattlerfid.view.utils;

import javax.swing.*;
import javax.swing.border.Border;
import java.awt.*;

public class UIStyles {

    // Color Palette
    // Color Palette - Premium Green
    public static final Color PRIMARY = new Color(5, 150, 105); // Emerald 600
    public static final Color PRIMARY_DARK = new Color(2, 44, 34); // Emerald 950 (Sidebar-like)
    public static final Color SECONDARY = new Color(71, 85, 105); // Slate 600
    public static final Color SUCCESS = new Color(5, 150, 105); // Using Emerald as Success too
    public static final Color DANGER = new Color(220, 38, 38); // Red 600
    public static final Color WARNING = new Color(251, 191, 36); // Amber 400
    public static final Color BACKGROUND = new Color(248, 250, 252); // Slate 50
    public static final Color TEXT_DARK = new Color(15, 23, 42); // Slate 900
    public static final Color TEXT_LIGHT = Color.WHITE;
    public static final Color TEXT_MUTED = new Color(100, 116, 139); // Slate 500

    // Typography
    public static final Font HEADER_FONT = new Font("Segoe UI", Font.BOLD, 22);
    public static final Font SUBHEADER_FONT = new Font("Segoe UI", Font.BOLD, 16);
    public static final Font BODY_FONT = new Font("Segoe UI", Font.PLAIN, 14);
    public static final Font LABEL_FONT = new Font("Segoe UI", Font.BOLD, 14);

    /**
     * Creates a styled title label.
     */
    public static JLabel createTitleLabel(String text) {
        JLabel label = new JLabel(text, SwingConstants.CENTER);
        label.setFont(HEADER_FONT);
        label.setForeground(PRIMARY);
        return label;
    }

    /**
     * Creates a standardized Primary Button (Blue/Green depending on context).
     */
    public static JButton createPrimaryButton(String text) {
        JButton btn = new JButton(text);
        btn.setFont(SUBHEADER_FONT);
        btn.setForeground(TEXT_LIGHT);
        btn.setBackground(PRIMARY);
        btn.setFocusPainted(false);
        btn.setCursor(new Cursor(Cursor.HAND_CURSOR));
        btn.setPreferredSize(new Dimension(200, 45));
        return btn;
    }

    /**
     * Creates a standardized Success Button (e.g., Save/Submit).
     */
    public static JButton createSuccessButton(String text) {
        JButton btn = createPrimaryButton(text);
        btn.setBackground(SUCCESS);
        return btn;
    }

    /**
     * Creates a back/cancel button.
     */
    public static JButton createBackButton(String text) {
        JButton btn = new JButton(text);
        btn.setFont(BODY_FONT);
        btn.setForeground(DANGER);
        btn.setBackground(Color.WHITE);
        btn.setFocusPainted(false);
        btn.setCursor(new Cursor(Cursor.HAND_CURSOR));
        return btn;
    }

    /**
     * Applies a clean shadow/card border to a panel.
     */
    public static Border createCardBorder() {
        return BorderFactory.createCompoundBorder(BorderFactory.createLineBorder(new Color(200, 200, 200), 1, true), BorderFactory.createEmptyBorder(20, 20, 20, 20));
    }
}
