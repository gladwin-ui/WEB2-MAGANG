import time
from typing import Optional, List

import uvicorn
from fastapi import FastAPI
from pydantic import BaseModel


from services.sentiment import analyze_sentiment
from services.report_clustering import cluster_reports


app = FastAPI(
    title="BugTrack MFG - AI Analytics Service",
    description="Manufacturing Bug Report Analysis: Sentiment & Clustering",
    version="2.0.0",
)


class BugReportRequest(BaseModel):
    text: str
    title: Optional[str] = None


class BugReportResponse(BaseModel):
    sentiment_label: Optional[str] = None
    sentiment_score: Optional[float] = None

    processing_time_ms: Optional[float] = None


class ClusterRequest(BaseModel):
    texts: List[str]


class ClusterItem(BaseModel):
    label: str
    count: int


class ClusterResponse(BaseModel):
    clusters: List[ClusterItem]


@app.post("/analyze-bug-report", response_model=BugReportResponse)
def analyze_bug_report(request: BugReportRequest):
    """Unified bug report analysis endpoint (sentiment, keyphrase)."""
    start_time = time.time()
    description = request.text
    title = request.title

    sentiment_label, sentiment_score = analyze_sentiment(description)


    processing_time = (time.time() - start_time) * 1000

    return BugReportResponse(
        sentiment_label=sentiment_label,
        sentiment_score=sentiment_score,

        processing_time_ms=round(processing_time, 2),
    )


@app.post("/cluster-reports", response_model=ClusterResponse)
def cluster_reports_endpoint(request: ClusterRequest):
    """Kelompokkan laporan mirip → top 5 kelompok."""
    result = cluster_reports(request.texts)
    return ClusterResponse(clusters=[ClusterItem(**c) for c in result])


@app.get("/health")
def health_check():
    """Health check endpoint."""
    return {
        "status": "healthy",
        "service": "BugTrack MFG Analytics",
        "version": "2.0.0",
        "models": {
            "sentiment": "keyword-based",
            "clustering": "overlap-connected-components"
        },
    }


if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)

