# Project Cost Estimation — KaAyos Home Service Platform

## System Implementation Cost Estimate

| Item | Quantity | Unit Cost (₱) | Total Cost (₱) | Status |
|------|----------|---------------|----------------|--------|
| Hostinger VPS KVM 2 (2 vCPU, 8GB RAM, 100GB NVMe, 8TB Bandwidth) | 1 | 1,600.48/month | 1,600.48 | Confirmed |
| OpenRouter AI API (openai/gpt-4o-mini) | Usage-based | 0.00015–0.00060/token | 21.00–1,071.00/month | Estimated |
| MySQL Database | 1 | 0.00 | 0.00 | Included with VPS |
| Laravel Reverb (WebSocket) | 1 | 0.00 | 0.00 | Self-hosted on VPS |
| ML Service (Python microservice) | 1 | 0.00 | 0.00 | Self-hosted on VPS |
| AWS S3 Storage | 1 | 0.00 | 0.00 | Not active |
| Redis Cache | 1 | 0.00 | 0.00 | Not configured |
| Google OAuth | 1 | 0.00 | 0.00 | Free |
| Facebook OAuth | 1 | 0.00 | 0.00 | Free |

## Total Monthly Implementation Cost

| Scenario | Monthly Cost (₱) |
|----------|------------------|
| Light (10–50 chatbot msgs/day) | 1,621.48 – 2,030.48 |
| Moderate (50–100 chatbot msgs/day) | 2,030.48 – 2,450.48 |
| Heavy (100–200 chatbot msgs/day) | 2,450.48 – 3,300.48 |

**Recommended single-number estimate: ₱2,200.00/month**

---

*All values in Philippine Peso (₱). OpenRouter cost varies with chatbot message volume. Free and self-hosted components incur no additional expense.*