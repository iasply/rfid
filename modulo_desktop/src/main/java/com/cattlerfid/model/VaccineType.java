package com.cattlerfid.model;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class VaccineType {

    private long id;
    private String name;
    private String description;

    @SerializedName("interval_days")
    private Integer intervalDays;

    @SerializedName("season_months")
    private List<Integer> seasonMonths;

    public VaccineType() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public Integer getIntervalDays() {
        return intervalDays;
    }

    public void setIntervalDays(Integer intervalDays) {
        this.intervalDays = intervalDays;
    }

    public List<Integer> getSeasonMonths() {
        return seasonMonths;
    }

    public void setSeasonMonths(List<Integer> seasonMonths) {
        this.seasonMonths = seasonMonths;
    }

    /**
     * Shown in the JComboBox dropdown.
     */
    @Override
    public String toString() {
        if (intervalDays != null) {
            return name + " (a cada " + intervalDays + " dias)";
        }
        return name;
    }
}
