// Package libdns implements a DNS record management client compatible
// with the libdns interfaces for jonasarensmann/web-panel.
package webpanellibdns

import (
	"context"
	"strings"

	"github.com/libdns/libdns"
)

// Provider facilitates DNS record manipulation with jonasarensmann/web-panel.
type Provider struct {
	// URL is the URL of the jonasarensmann/web-panel instance.
	URL string `json:"url,omitempty"`
	// APIToken is the API token used to authenticate with the instance.
	APIToken string `json:"api_token,omitempty"`
}

// GetRecords lists all the records in the zone.
func (p *Provider) GetRecords(ctx context.Context, zone string) ([]libdns.Record, error) {
	zone = strings.TrimSuffix(zone, ".")

	records, err := p.getZoneRecords(zone)
	if err != nil {
		return nil, err
	}

	return records, nil
}

// AppendRecords adds records to the zone. It returns the records that were added.
func (p *Provider) AppendRecords(ctx context.Context, zone string, records []libdns.Record) ([]libdns.Record, error) {
	zone = strings.TrimSuffix(zone, ".")

	var created []libdns.Record
	for _, rec := range records {
		result, err := p.appendZoneRecord(zone, rec)
		if err != nil {
			return nil, err
		}
		created = append(created, result)
	}

	return created, nil
}

// SetRecords sets the records in the zone, either by updating existing records or creating new ones.
// It returns the updated records.
func (p *Provider) SetRecords(ctx context.Context, zone string, records []libdns.Record) ([]libdns.Record, error) {
	zone = strings.TrimSuffix(zone, ".")

	var updated []libdns.Record
	for _, rec := range records {
		result, err := p.setZoneRecord(zone, rec)
		if err != nil {
			return nil, err
		}
		updated = append(updated, result)
	}

	return updated, nil
}

// DeleteRecords deletes the records from the zone. It returns the records that were deleted.
func (p *Provider) DeleteRecords(ctx context.Context, zone string, records []libdns.Record) ([]libdns.Record, error) {
	zone = strings.TrimSuffix(zone, ".")

	var deleted []libdns.Record
	for _, rec := range records {
		result, err := p.deleteZoneRecord(zone, rec)
		if err != nil {
			return nil, err
		}
		deleted = append(deleted, result)
	}

	return deleted, nil
}

// Interface guards
var (
	_ libdns.RecordGetter   = (*Provider)(nil)
	_ libdns.RecordAppender = (*Provider)(nil)
	_ libdns.RecordSetter   = (*Provider)(nil)
	_ libdns.RecordDeleter  = (*Provider)(nil)
)
