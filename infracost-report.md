
# Project: main

| Name                                                        | Monthly Qty | Unit                          | Monthly Cost         |
|-------------------------------------------------------------|-------------|-------------------------------|----------------------|
| aws_elastic_beanstalk_environment.php_app_env                |             |                               |                      |
| ├─ aws_launch_configuration                                  |             |                               |                      |
| │  ├─ Instance usage (Linux/UNIX, on-demand, )               | 730         | hours                         | not found            |
| │  └─ aws_ebs_volume                                         |             |                               |                      |
| │     └─ Storage (general purpose SSD, gp2)                  | 8           | GB                            | $0.80                |
| └─ aws_loadbalancer                                          |             |                               |                      |
|    ├─ Network load balancer                                  | 730         | hours                         | $16.43               |
|    └─ Load balancer capacity units                           |             | Monthly cost depends on usage | $4.38 per LCU       |
| **OVERALL TOTAL**                                            |             |                               | **$17.23**           |

> Usage costs can be estimated by updating Infracost Cloud settings, see docs for other options.

---

2 cloud resources were detected:
- 1 was estimated
- 1 was free

# Costs Summary

| Project                                                    | Baseline cost | Usage cost* | Total cost  |
|------------------------------------------------------------|---------------|-------------|-------------|
| main                                                       | $17           | -           | $17         |
