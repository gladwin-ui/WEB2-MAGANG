import time
from typing import Optional

import uvicorn
from fastapi import FastAPI
from pydantic import BaseModel


from services.sentiment import analyze_sentiment
from services.severity_recommendation import recommend_severity


app = FastAPI(
    title="BugTrack MFG - AI Analytics Service",
    description="Manufacturing Bug Report Analysis: Severity, Sentiment",
    version="2.0.0",
)


class BugReportRequest(BaseModel):
    text: str
    title: Optional[str] = None


class BugReportResponse(BaseModel):
    sentiment_label: Optional[str] = None
    sentiment_score: Optional[float] = None
    severity_recommended: Optional[str] = None
    severity_recommendation_reason: Optional[str] = None

    processing_time_ms: Optional[float] = None


@app.post("/analyze-bug-report", response_model=BugReportResponse)
def analyze_bug_report(request: BugReportRequest):
    """Unified bug report analysis endpoint (sentiment, severity, keyphrase)."""
    start_time = time.time()
    description = request.text
    title = request.title

    sentiment_label, sentiment_score = analyze_sentiment(description)
    severity_rec, severity_reason = recommend_severity(description)


    processing_time = (time.time() - start_time) * 1000

    return BugReportResponse(
        sentiment_label=sentiment_label,
        sentiment_score=sentiment_score,
        severity_recommended=severity_rec,
        severity_recommendation_reason=severity_reason,

        processing_time_ms=round(processing_time, 2),
    )


@app.get("/health")
def health_check():
    """Health check endpoint."""
    return {
        "status": "healthy",
        "service": "BugTrack MFG Analytics",
        "version": "2.0.0",
        "models": {
            "sentiment": "keyword-based",
            "severity": "rule-based"
        },
    }


if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
