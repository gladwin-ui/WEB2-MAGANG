import time
from typing import List, Optional

import uvicorn
from fastapi import FastAPI
from pydantic import BaseModel, Field

from services.damage_categorization import categorize_damage
from services.duplicate_detection import find_duplicates
from services.sentiment import analyze_sentiment
from services.severity_recommendation import recommend_severity
from services.spam_detection import detect_spam

app = FastAPI(
    title="BugTrack MFG - AI Analytics Service",
    description="Manufacturing Bug Report Analysis: Spam Detection, Severity, Sentiment, Duplicate Detection",
    version="2.0.0",
)


class BugReportRequest(BaseModel):
    text: str
    bug_id: Optional[int] = None


class DuplicateMatch(BaseModel):
    bug_id: int
    similarity_score: float
    title: str


class BugReportResponse(BaseModel):
    is_spam: bool
    spam_confidence: Optional[float] = None
    spam_reason: Optional[str] = None
    spam_tier: Optional[str] = None
    sentiment_label: Optional[str] = None
    sentiment_score: Optional[float] = None
    severity_recommended: Optional[str] = None
    severity_recommendation_reason: Optional[str] = None
    duplicates: List[DuplicateMatch] = Field(default_factory=list)
    duplicate_count: int = 0
    processing_time_ms: Optional[float] = None


class DamageCauseRequest(BaseModel):
    root_cause: Optional[str] = ""
    repair_action: Optional[str] = ""


class DamageCauseResponse(BaseModel):
    damage_category: str


@app.post("/analyze-bug-report", response_model=BugReportResponse)
async def analyze_bug_report(request: BugReportRequest):
    """Unified bug report analysis endpoint with spam-first early return."""
    start_time = time.time()
    description = request.text

    is_spam, spam_reason, spam_confidence, spam_tier = detect_spam(description)

    if is_spam and (spam_confidence is None or spam_confidence > 0.60):
        processing_time = (time.time() - start_time) * 1000
        return BugReportResponse(
            is_spam=True,
            spam_confidence=spam_confidence,
            spam_reason=spam_reason,
            spam_tier=spam_tier,
            sentiment_label="spam",
            duplicates=[],
            duplicate_count=0,
            processing_time_ms=round(processing_time, 2),
        )

    sentiment_label, sentiment_score = analyze_sentiment(description)
    severity_rec, severity_reason = recommend_severity(description)
    duplicates_list, duplicate_count = find_duplicates(description, request.bug_id)

    duplicate_matches = [
        DuplicateMatch(
            bug_id=dup["bug_id"],
            similarity_score=dup["similarity_score"],
            title=dup["title"],
        )
        for dup in duplicates_list
    ]

    processing_time = (time.time() - start_time) * 1000

    return BugReportResponse(
        is_spam=False,
        spam_confidence=spam_confidence,
        spam_reason=spam_reason,
        spam_tier=spam_tier,
        sentiment_label=sentiment_label,
        sentiment_score=sentiment_score,
        severity_recommended=severity_rec,
        severity_recommendation_reason=severity_reason,
        duplicates=duplicate_matches,
        duplicate_count=duplicate_count,
        processing_time_ms=round(processing_time, 2),
    )


@app.post("/analyze-damage-cause", response_model=DamageCauseResponse)
def analyze_damage_cause(request: DamageCauseRequest):
    """Analyze root cause and repair action into damage category."""
    category = categorize_damage(request.root_cause, request.repair_action)
    return DamageCauseResponse(damage_category=category)


@app.get("/health")
def health_check():
    """Health check endpoint."""
    return {
        "status": "healthy",
        "service": "BugTrack MFG Analytics",
        "version": "2.0.0",
        "models": {
            "spam_detection": "4-tier (rules + ML)",
            "sentiment": "keyword-based",
            "severity": "rule-based",
            "duplicate": "placeholder",
        },
    }


if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
