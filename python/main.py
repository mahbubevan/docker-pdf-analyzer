from fastapi import FastAPI

app = FastAPI(
    title = "PDF Analyzer Service",
    version = "1.0.0"
)

@app.get("/")
def root():
    return {
        "service" : "PDF ANALYZER Python Service",
        "status" : "Running"
    }

@app.get("/health")
def health():
    return {
        "status":"OK",
        "message":"Live bind mounted works"
    }