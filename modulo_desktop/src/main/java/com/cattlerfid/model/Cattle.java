package com.cattlerfid.model;

import com.google.gson.annotations.SerializedName;

public class Cattle {

    private int id;

    @SerializedName("rfid_tag")
    private String rfidTag;

    private String name;
    private double weight;

    @SerializedName("registration_date")
    private String registrationDate;

    @SerializedName("vaccines_count")
    private int vaccinesCount;

    public Cattle() {
    }

    public Cattle(String rfidTag, String name, double weight, String registrationDate) {
        this.rfidTag = rfidTag;
        this.name = name;
        this.weight = weight;
        this.registrationDate = registrationDate;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getRfidTag() {
        return rfidTag;
    }

    public void setRfidTag(String rfidTag) {
        if (rfidTag != null && rfidTag.length() > 16) {
            this.rfidTag = rfidTag.substring(0, 16);
        } else {
            this.rfidTag = rfidTag;
        }
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public double getWeight() {
        return weight;
    }

    public void setWeight(double weight) {
        this.weight = weight;
    }

    public String getRegistrationDate() {
        return registrationDate;
    }

    public void setRegistrationDate(String registrationDate) {
        this.registrationDate = registrationDate;
    }

    public int getVaccinesCount() {
        return vaccinesCount;
    }

    public void setVaccinesCount(int vaccinesCount) {
        this.vaccinesCount = vaccinesCount;
    }
}
