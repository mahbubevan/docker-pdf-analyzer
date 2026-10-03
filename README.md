# Docker PDF Analyzer

A production-style, multi-service **PDF Analyzer** built with **Laravel, Python FastAPI, Node.js, MySQL, Valkey, Nginx, and Docker**.

The project is designed as a practical Docker and DevOps learning environment. Instead of learning containers through isolated examples, it uses a real application where services communicate, process background jobs, persist data, broadcast realtime progress, scale horizontally, and eventually deploy through CI/CD.

---

## Overview

Users upload a PDF through the Laravel application.

The document is stored, queued for background processing, analyzed by a Python FastAPI service, saved to MySQL, and its processing progress is broadcast to the browser through Node.js realtime events.

```text
User
  |
  v
Laravel
  |
  +----> MySQL
  |
  +----> Valkey Queue
             |
             v
      Laravel Worker
             |
       +-----+-----+
       |           |
       v           v
   FastAPI      Node.js
 PDF Analyzer   Realtime
       |           |
       +-----+-----+
             |
             v
          Browser
```

---

## Why This Project Exists

The main goal is not simply to build a PDF analyzer.

The project exists to understand how a real multi-service application works with Docker and modern DevOps practices.

Topics explored throughout the project include:

- Docker images
- Containers
- Dockerfiles
- Build context
- Docker Compose
- Internal Docker networking
- Service discovery
- Host ports vs container ports
- Bind mounts
- Named volumes
- Persistent storage
- Queue workers
- Scheduler containers
- Health checks
- Restart policies
- Horizontal scaling
- Load balancing
- Development vs production environments
- Docker registries
- GitHub Actions
- CI/CD
- Cross-machine deployment
- AWS deployment
- Kubernetes concepts later

---

## Tech Stack

| Component | Technology | Responsibility |
|---|---|---|
| Main Application | Laravel | UI, authentication, uploads, business logic, orchestration |
| PDF Processing | Python + FastAPI | PDF extraction, analysis, statistics, structure detection |
| Realtime Server | Node.js | WebSocket / Socket.IO progress events |
| Database | MySQL | Persistent application data |
| Queue / Cache | Valkey | Laravel queue backend and optional cache |
| Web Server | Nginx | Reverse proxy and later load balancing |
| Container Runtime | Docker | Service isolation and packaging |
| Multi-service Orchestration | Docker Compose | Local and production-style service management |

---

## Application Flow

```text
PDF uploaded
    |
    v
Laravel validates file
    |
    v
PDF stored persistently
    |
    v
Analysis record created
    |
    v
Job dispatched to Valkey
    |
    v
Laravel worker receives job
    |
    v
Python FastAPI analyzes PDF
    |
    v
Structured JSON returned
    |
    v
Laravel stores results
    |
    v
Node.js broadcasts progress
    |
    v
Browser updates in realtime
```

---

## Main Features

### PDF Upload

- Modern drag-and-drop interface
- PDF validation
- File metadata
- Optional document title
- Persistent PDF storage

### Background Processing

Long-running analysis is handled asynchronously.

```text
Laravel
   |
   v
Valkey
   |
   v
Laravel Worker
   |
   v
FastAPI
```

### Realtime Progress

The UI can display stages such as:

```text
PDF uploaded
Task queued
Worker started
Extracting text
Analyzing document
Structuring data
Saving result
Completed
```

Realtime events are delivered through Node.js using WebSocket / Socket.IO communication.

### Structured Analysis

The final result can include:

- File name
- Page count
- File size
- Detected title
- Document type
- Language
- Word count
- Character count
- Reading time
- Headings
- Sections
- Paragraphs
- Tables when detectable
- Important dates
- Numbers
- Amounts
- Emails
- Phone numbers
- URLs
- Keywords
- Named entities where appropriate
- Document summary
- Raw extracted text

---

## Service Responsibilities

### Laravel

Laravel is the main application and owns the business data.

Responsibilities:

- User interface
- Authentication
- PDF uploads
- Validation
- Database records
- Queue dispatch
- Analysis history
- Analysis result presentation
- Service orchestration

Heavy PDF processing is intentionally delegated to Python.

---

### Python FastAPI

Python acts as the document-processing engine.

Responsibilities:

- PDF text extraction
- Page counting
- Text cleanup
- Document statistics
- Keyword extraction
- Section detection
- Structure extraction
- Basic document analysis

Example endpoints:

```http
GET /health
POST /analyze
```

Inside Docker, Laravel/worker services communicate with Python through:

```text
http://python:8000
```

---

### Node.js

Node.js handles realtime communication.

Responsibilities:

- WebSocket / Socket.IO server
- Processing progress events
- Completion notifications
- Failure notifications

Example internal endpoint:

```text
http://node:3000/event
```

---

### MySQL

MySQL stores persistent application data including:

- PDF metadata
- Processing state
- Progress
- Analysis results
- Extracted statistics
- Structured JSON
- Errors
- Processing timestamps

---

### Valkey

Valkey is used as Redis-compatible infrastructure.

Primary responsibilities:

- Laravel queue backend
- Optional application cache

Example Laravel configuration:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=valkey
REDIS_PORT=6379
```

---

## Project Structure

```text
docker-pdf-analyzer/
|
├── laravel/
│   ├── Dockerfile
│   └── Laravel source
│
├── python/
│   ├── Dockerfile
│   ├── main.py
│   ├── requirements.txt
│   └── analyzer/
│
├── node/
│   ├── Dockerfile
│   ├── server.js
│   ├── package.json
│   └── package-lock.json
│
├── nginx/
│   └── default.conf
│
├── compose.yml
├── compose.prod.yml
├── .env.example
└── README.md
```

The exact structure may evolve as the project progresses.

---

## Docker Networking

Containers communicate using Docker service names instead of host-published ports.

Examples:

```text
Laravel -> mysql:3306
Laravel -> valkey:6379
Worker  -> python:8000
Worker  -> node:3000
```

An important concept demonstrated by the project is that:

```text
localhost
```

inside a container refers to that container itself.

---

## Persistence

Containers are disposable.

Persistent application data must therefore live outside the container's writable layer.

The project uses persistent storage for:

- MySQL data
- Uploaded PDFs
- Processed files when required

A major learning exercise is intentionally destroying and recreating containers while verifying that important data remains available.

---

## Queue Workers

The project uses a dedicated Laravel worker container.

Example process:

```bash
php artisan queue:work
```

The worker:

1. Receives jobs from Valkey
2. Updates analysis progress
3. Calls the Python service
4. Receives structured analysis data
5. Stores results in MySQL
6. Sends realtime events to Node.js

Laravel web, worker, and scheduler containers should normally reuse the same Laravel image with different startup commands.

---

## Scheduler

A dedicated scheduler container may later run:

```bash
php artisan schedule:work
```

Example scheduled tasks:

- Clean abandoned temporary uploads
- Delete expired analysis files

The project also explores the difference between:

```text
schedule:work
schedule:run
cron
```

---

## Horizontal Scaling

One of the later learning stages is scaling workers.

Example:

```bash
docker compose up -d --scale worker=3
```

This allows several PDFs to be processed concurrently.

Laravel application instances may also be scaled later behind Nginx.

```text
             Nginx
               |
      +--------+--------+
      |        |        |
  Laravel  Laravel  Laravel
      |        |        |
      +--------+--------+
               |
       Shared Services
        MySQL / Valkey
```

---

## Development vs Production

### Development

Development environments may use:

- Local image builds
- Bind mounts
- Source-code editing
- Published debugging ports
- Development environment variables

### Production

Production should prefer:

- Prebuilt Docker images
- Production environment variables
- Persistent volumes
- Minimal exposed ports
- Health checks
- Restart policies
- Image-based deployments

A final production-style deployment should not require PHP, Composer, Python, pip, Node.js, npm, MySQL, Valkey, or Nginx to be installed directly on the destination machine.

Docker provides the runtime environment.

---

## Source Repository vs Container Registry

The project separates source-code storage from runtime images.

```text
GitHub
  |
  +--> Source code
  |
  v
CI/CD
  |
  v
Docker Registry
  |
  +--> Built images
  |
  v
Production Server
```

Possible registries include:

- Docker Hub
- Amazon ECR

---

## CI/CD Goal

The planned CI/CD pipeline is:

```text
git push
   |
   v
GitHub Actions
   |
   v
Automated Tests
   |
   v
Docker Image Build
   |
   v
Container Registry
   |
   v
Deployment
```

CI and CD are introduced only after the core Docker concepts are understood.

---

## AWS Learning Path

After cross-machine Docker deployment works successfully, the project can move to AWS.

Planned progression:

```text
EC2 + Docker Compose
        |
        v
Amazon ECR
        |
        v
RDS MySQL
        |
        v
ElastiCache / Redis-compatible service
        |
        v
Amazon S3
        |
        v
Application Load Balancer
```

Kubernetes / Amazon EKS is intentionally postponed until Docker and AWS fundamentals are clear.

---

## Learning Roadmap

The project is developed incrementally.

- [x] Define project architecture and learning goals
- [ ] Run Laravel, Python, and Node.js directly on the host
- [ ] Establish Laravel -> Python communication
- [ ] Establish Laravel -> Node.js communication
- [ ] Dockerize Python
- [ ] Dockerize Node.js
- [ ] Dockerize Laravel
- [ ] Run containers manually
- [ ] Introduce Docker Compose
- [ ] Configure Docker networking
- [ ] Add MySQL persistence
- [ ] Add persistent PDF storage
- [ ] Configure Valkey queues
- [ ] Add Laravel worker container
- [ ] Implement Python PDF analysis
- [ ] Add realtime Node.js updates
- [ ] Make MySQL the reliable source of truth
- [ ] Add health checks
- [ ] Add scheduler container
- [ ] Scale workers
- [ ] Scale Laravel
- [ ] Configure Nginx load balancing
- [ ] Separate development and production Compose files
- [ ] Build production images
- [ ] Push images to a container registry
- [ ] Deploy on another machine
- [ ] Explore multi-architecture images
- [ ] Add GitHub Actions CI/CD
- [ ] Deploy to AWS
- [ ] Explore Kubernetes concepts

---

## Getting Started

The project is actively being developed, so installation instructions will evolve with the implementation.

When the Docker Compose setup is complete, the intended workflow will be similar to:

```bash
git clone <repository-url>

cd docker-pdf-analyzer

cp .env.example .env

docker compose up -d --build
```

Additional Laravel setup commands may be required depending on the current development stage.

Check the repository history and documentation before running the project.

---

## Environment Variables

Never commit real secrets or production environment files.

Recommended `.gitignore` rules:

```gitignore
.env
.env.*
!.env.example
```

Only safe example configuration should be committed:

```text
.env.example
```

---

## Useful Docker Commands

Some commands explored throughout this project include:

```bash
docker build
docker run
docker ps
docker ps -a
docker logs
docker exec
docker stop
docker start
docker rm
docker rmi
```

Docker Compose commands include:

```bash
docker compose up -d
docker compose up -d --build
docker compose ps
docker compose logs
docker compose logs -f worker
docker compose exec laravel bash
docker compose down
docker compose pull
docker compose up -d --scale worker=3
```

Destructive commands such as the following should be used carefully:

```bash
docker compose down -v
docker system prune -a
```

They may remove persistent volumes, unused images, containers, networks, or other Docker resources depending on the command.

---

## Design Principles

### One Responsibility Per Service

Laravel, Python, Node.js, MySQL, Valkey, and Nginx remain separate services.

### Reusable Images

Laravel web, queue worker, and scheduler processes reuse the same Laravel application image where appropriate.

### Persistent Data Outside Containers

Containers can be destroyed and recreated without losing important application data.

### Internal Service Discovery

Container-to-container communication uses Docker DNS service names.

### Database as Source of Truth

Realtime events improve UX, but MySQL stores the reliable application state.

### Small Application Scope

The application intentionally avoids unrelated features such as:

- Billing
- Subscriptions
- Affiliate systems
- Large admin panels
- Complex permission systems
- Multi-tenancy
- Unnecessary AI integrations

The focus is Docker, service architecture, and DevOps.

---

## Contributing

Contributions, suggestions, bug reports, and learning-focused improvements are welcome.

If you would like to contribute:

1. Fork the repository
2. Create a feature branch
3. Make focused changes
4. Test your changes
5. Commit with a clear message
6. Open a pull request

Please keep contributions aligned with the project's main goal: learning Docker and DevOps through a small, understandable real-world application.

---

## Security

Please do not publish:

- Production `.env` files
- API keys
- Database passwords
- Cloud credentials
- Private SSH keys
- Registry credentials
- Other secrets

If a security issue is discovered, avoid posting sensitive exploit details publicly before the issue can be reviewed.

---

## Project Status

🚧 **Active Development / Learning Project**

The application is being built incrementally. Some functionality described in this README represents the planned architecture and may not yet be implemented.

---

## Educational Use

This repository is intended to be useful for developers learning:

- Docker
- Multi-container architecture
- Laravel queues
- FastAPI services
- Realtime communication
- Persistent storage
- Scaling
- CI/CD
- Production deployment fundamentals

Feel free to explore the architecture, experiment with the containers, and adapt the concepts for your own learning projects.

---

## License

A license has not yet been selected.

If this repository is intended for broad reuse or contributions, consider adding an open-source license such as MIT, Apache-2.0, or another license appropriate for the project.