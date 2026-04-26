package com.cattlerfid.model;

import java.util.List;

public class PagedResult<T> {

    private final List<T> data;
    private final int currentPage;
    private final int lastPage;
    private final int total;
    private final int perPage;

    public PagedResult(List<T> data, int currentPage, int lastPage, int total, int perPage) {
        this.data = data;
        this.currentPage = currentPage;
        this.lastPage = lastPage;
        this.total = total;
        this.perPage = perPage;
    }

    public List<T> getData() {
        return data;
    }

    public int getCurrentPage() {
        return currentPage;
    }

    public int getLastPage() {
        return lastPage;
    }

    public int getTotal() {
        return total;
    }

    public int getPerPage() {
        return perPage;
    }

    public boolean hasNextPage() {
        return currentPage < lastPage;
    }

    public boolean hasPrevPage() {
        return currentPage > 1;
    }
}
