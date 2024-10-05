package webpanellibdns

import (
	"encoding/json"
	"strings"

	"github.com/imroc/req/v3"
	"github.com/libdns/libdns"
)

func getZoneIdByName(zone string, p *Provider) (string, error) {
	client := req.C().
		SetBaseURL(p.URL + "/api/dns/getByName")

	id := client.Get().
		SetHeader("Accept", "application/json").
		SetHeader("Authorization", "Bearer "+p.APIToken).
		SetQueryParam("name", zone).
		Do().String()

	GetLog().Println("Zone ID: ", id)

	return id, nil
}

func (p *Provider) getZoneRecords(zone string) ([]libdns.Record, error) {
	id, err := getZoneIdByName(zone, p)
	if err != nil {
		GetLog().Println("Error: ", err)
		return nil, err
	}

	client := req.C().
		SetBaseURL(p.URL + "/api/dns/" + id)

	var srecords []interface{}

	err = client.Get().
		SetHeader("Accept", "application/json").
		SetHeader("Authorization", "Bearer "+p.APIToken).
		Do().Into(&srecords)

	if err != nil {
		GetLog().Println("Error: ", err)
		return nil, err
	}

	GetLog().Println("Zone Records: ", srecords)

	records := []libdns.Record{}

	for _, srecord := range srecords {
		record := libdns.Record{
			Name:  srecord.(map[string]interface{})["name"].(string),
			Type:  srecord.(map[string]interface{})["type"].(string),
			Value: srecord.(map[string]interface{})["value"].(string),
		}

		records = append(records, record)
	}

	return records, nil
}

func (p *Provider) appendZoneRecord(zone string, record libdns.Record) (libdns.Record, error) {
	id, err := getZoneIdByName(zone, p)
	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	client := req.C().
		SetBaseURL(p.URL + "/api/dns/" + id)

	recordJSON, err := json.Marshal(map[string]interface{}{
		"name":  record.Name,
		"type":  strings.ToLower(record.Type),
		"value": "\"" + record.Value + "\"",
	})

	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	res := client.Post().
		SetHeader("Accept", "application/json").
		SetHeader("Content-Type", "application/json").
		SetHeader("Authorization", "Bearer "+p.APIToken).
		SetBodyString(string(recordJSON)).
		Do().String()

	GetLog().Println("Record: ", res)

	return record, nil
}

func (p *Provider) setZoneRecord(zone string, record libdns.Record) (libdns.Record, error) {
	id, err := getZoneIdByName(zone, p)
	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	client := req.C().
		SetBaseURL(p.URL + "/api/dns/" + id)

	recordJSON, err := json.Marshal(map[string]interface{}{
		"name":  record.Name,
		"type":  record.Type,
		"value": record.Value,
	})

	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	res := client.Patch().
		SetHeader("Accept", "application/json").
		SetHeader("Authorization", "Bearer "+p.APIToken).
		SetBodyString(string(recordJSON)).
		Do().String()

	GetLog().Println("[Patch]: Res", res)

	return record, nil
}

func (p *Provider) deleteZoneRecord(zone string, record libdns.Record) (libdns.Record, error) {
	id, err := getZoneIdByName(zone, p)
	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	client := req.C().
		SetBaseURL(p.URL + "/api/dns/" + id)

	recordJson, err := json.Marshal(map[string]interface{}{
		"records": []string{
			strings.ToLower(record.Type) + "§" + record.Name,
		},
	})

	GetLog().Println("Record JSON: ", recordJson)

	if err != nil {
		GetLog().Println("Error: ", err)
		return libdns.Record{}, err
	}

	res := client.Delete().
		SetHeader("Accept", "application/json").
		SetHeader("Content-Type", "application/json").
		SetHeader("Authorization", "Bearer "+p.APIToken).
		SetBody(string(recordJson)).
		Do().String()

	GetLog().Println("[Delete] Res: ", res)

	return record, nil
}
