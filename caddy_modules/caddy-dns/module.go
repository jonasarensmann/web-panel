package template

import (
	"github.com/caddyserver/caddy/v2"
	"github.com/caddyserver/caddy/v2/caddyconfig/caddyfile"
	webpanellibdns "github.com/jonasarensmann/web-panel/libdns"
)

// Provider lets Caddy read and manipulate DNS records hosted by this DNS provider.
type Provider struct{ *webpanellibdns.Provider }

func init() {
	caddy.RegisterModule(Provider{})
}

// CaddyModule returns the Caddy module information.
func (Provider) CaddyModule() caddy.ModuleInfo {
	return caddy.ModuleInfo{
		ID:  "dns.providers.webpanel",
		New: func() caddy.Module { return &Provider{new(webpanellibdns.Provider)} },
	}
}

// TODO: This is just an example. Useful to allow env variable placeholders; update accordingly.
// Provision sets up the module. Implements caddy.Provisioner.
func (p *Provider) Provision(ctx caddy.Context) error {
	p.Provider.APIToken = caddy.NewReplacer().ReplaceAll(p.Provider.APIToken, "")
	p.Provider.URL = caddy.NewReplacer().ReplaceAll(p.Provider.URL, "")

	return nil
}

// TODO: This is just an example. Update accordingly.
// UnmarshalCaddyfile sets up the DNS provider from Caddyfile tokens. Syntax:
//
//	webpanel {
//	    host <host_url>
//	    key <api_key>
//	}
//
// **THIS IS JUST AN EXAMPLE AND NEEDS TO BE CUSTOMIZED.**
func (p *Provider) UnmarshalCaddyfile(d *caddyfile.Dispenser) error {
	for d.Next() {
		if d.NextArg() {
			return d.ArgErr()
		}
		for nesting := d.Nesting(); d.NextBlock(nesting); {
			switch d.Val() {
			case "host":
				if p.Provider.URL != "" {
					return d.Err("host already set")
				}
				if d.NextArg() {
					p.Provider.URL = d.Val()
				}
				if d.NextArg() {
					return d.ArgErr()
				}
			case "key":
				if p.Provider.APIToken != "" {
					return d.Err("key already set")
				}
				if d.NextArg() {
					p.Provider.APIToken = d.Val()
				}
				if d.NextArg() {
					return d.ArgErr()
				}
			default:
				return d.Errf("unrecognized subdirective '%s'", d.Val())
			}
		}
	}
	if p.Provider.URL == "" {
		return d.Err("missing host")
	}
	if p.Provider.APIToken == "" {
		return d.Err("missing login key")
	}
	return nil
}

// Interface guards
var (
	_ caddyfile.Unmarshaler = (*Provider)(nil)
	_ caddy.Provisioner     = (*Provider)(nil)
)
