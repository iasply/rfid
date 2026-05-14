package com.cattlerfid.util;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.concurrent.atomic.AtomicLong;

public final class DebounceUtil {

    public static final long DEFAULT_MS = 800L;
    public static final long NAV_MS = 300L;

    private DebounceUtil() {}

    public static ActionListener debounce(ActionListener delegate) {
        return debounce(delegate, DEFAULT_MS);
    }

    public static ActionListener debounce(final ActionListener delegate, final long delayMs) {
        final AtomicLong lastFired = new AtomicLong(0L);
        return new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                long now = System.currentTimeMillis();
                if (now - lastFired.get() >= delayMs) {
                    lastFired.set(now);
                    delegate.actionPerformed(e);
                }
            }
        };
    }
}
