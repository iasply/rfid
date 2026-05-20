package com.cattlerfid.model;

public class User {

    private int id;
    private String name;
    private String email;
    private String vet_rfid;
    private boolean is_veterinarian;

    private transient String accessToken;

    public User() {
    }

    public User(String username, String fullName) {
        this.vet_rfid = username;
        this.name = fullName;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getVetRfid() {
        return vet_rfid;
    }

    public void setVetRfid(String v) {
        if (v != null && v.length() > 16) {
            this.vet_rfid = v.substring(0, 16);
        } else {
            this.vet_rfid = v;
        }
    }

    public boolean isVeterinarian() {
        return is_veterinarian;
    }

    public void setVeterinarian(boolean v) {
        this.is_veterinarian = v;
    }

    public String getAccessToken() {
        return accessToken;
    }

    public void setAccessToken(String t) {
        this.accessToken = t;
    }

}
