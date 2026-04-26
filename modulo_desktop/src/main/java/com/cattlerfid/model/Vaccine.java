package com.cattlerfid.model;

import com.google.gson.annotations.SerializedName;


public class Vaccine {
    private String id;

    @SerializedName("rfid_tag")
    private String rfidTag;

    @SerializedName("vaccination_date")
    private String vaccinationDate;

    @SerializedName("vaccine_type_id")
    private long vaccineTypeId;

    @SerializedName("vaccine_type_name")
    private String vaccineTypeName;

    @SerializedName("current_weight")
    private double currentWeight;

    @SerializedName("veterinarian_name")
    private String veterinarianName;

    @SerializedName("workstation_desc")
    private String workstationDesc;

    public Vaccine() {}

    public Vaccine(String id, String rfidTag, String vaccinationDate, long vaccineTypeId, double currentWeight) {
        this.id            = id;
        this.rfidTag       = rfidTag;
        this.vaccinationDate = vaccinationDate;
        this.vaccineTypeId = vaccineTypeId;
        this.currentWeight = currentWeight;
    }

    public String getId() { return id; }
    public void setId(String id) { this.id = id; }

    public String getRfidTag() { return rfidTag; }
    public void setRfidTag(String rfidTag) { this.rfidTag = rfidTag; }

    public String getVaccinationDate() { return vaccinationDate; }
    public void setVaccinationDate(String vaccinationDate) { this.vaccinationDate = vaccinationDate; }

    public long getVaccineTypeId() { return vaccineTypeId; }
    public void setVaccineTypeId(long vaccineTypeId) { this.vaccineTypeId = vaccineTypeId; }

    public String getVaccineTypeName() { return vaccineTypeName; }
    public void setVaccineTypeName(String vaccineTypeName) { this.vaccineTypeName = vaccineTypeName; }

    public double getCurrentWeight() { return currentWeight; }
    public void setCurrentWeight(double currentWeight) { this.currentWeight = currentWeight; }

    public String getVeterinarianName() { return veterinarianName; }
    public void setVeterinarianName(String veterinarianName) { this.veterinarianName = veterinarianName; }

    public String getWorkstationDesc() { return workstationDesc; }
    public void setWorkstationDesc(String workstationDesc) { this.workstationDesc = workstationDesc; }
}
