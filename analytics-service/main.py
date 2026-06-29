import uvicorn
from fastapi import FastAPI
from pydantic import BaseModel
from typing import Optional

from services.sentiment import analyze_sentiment
from services.spam_detection import is_spam_report
from services.severity_recommendation import recommend_severity
from services.damage_categorization import categorize_damage

app = FastAPI(title="Manufacturing Tracking System by PT Hariff Analytics AI Service")

# Request Models
class BugReportRequest(BaseModel):
    text: str
    gemini_api_key: Optional[str] = None
    gemini_model: Optional[str] = "gemini-2.0-flash-lite"

class BugReportResponse(BaseModel):
    sentiment_label: Optional[str]
    sentiment_score: Optional[float]
    is_spam: bool
    spam_reason: Optional[str]
    severity_recommended: str
    severity_recommendation_reason: str

class DamageCauseRequest(BaseModel):
    root_cause: Optional[str] = ""
    repair_action: Optional[str] = ""

class DamageCauseResponse(BaseModel):
    damage_category: str

@app.post("/analyze-bug-report", response_model=BugReportResponse)
def analyze_bug_report(request: BugReportRequest):
    description = request.text
    
    # 1. Spam Detection (with confidence)
    is_spam, spam_reason, spam_confidence = is_spam_report(
        description, 
        request.gemini_api_key, 
        request.gemini_model
    )
    
    # 2. Sentiment Analysis
    sentiment_label, sentiment_score = analyze_sentiment(description)
    
    # If spam detected (high confidence), force sentiment to spam
    if is_spam and (spam_confidence is None or spam_confidence > 0.65):
        sentiment_label = "spam"
    elif is_spam and spam_confidence and spam_confidence < 0.65:
        sentiment_label = "uncertain_spam"  # Flag for manual review
    
    # 3. Severity Recommendation
    severity_rec, severity_reason = recommend_severity(description)
    
    return BugReportResponse(
        sentiment_label=sentiment_label,
        sentiment_score=sentiment_score,
        is_spam=is_spam,
        spam_reason=spam_reason,
        severity_recommended=severity_rec,
        severity_recommendation_reason=severity_reason
    )

@app.post("/analyze-damage-cause", response_model=DamageCauseResponse)
def analyze_damage_cause(request: DamageCauseRequest):
    root_cause = request.root_cause
    repair_action = request.repair_action
    
    # 4. Damage Categorization
    category = categorize_damage(root_cause, repair_action)
    
    return DamageCauseResponse(damage_category=category)

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
