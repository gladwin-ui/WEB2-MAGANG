"""
Duplicate Bug Report Detection (Placeholder - Phase 3)

Akan diimplementasi:
- TF-IDF similarity matching terhadap bug reports existing di database
- Cosine similarity threshold untuk flagging potential duplicates

Saat ini: return empty results.
"""

from typing import Dict, List, Optional, Tuple


def find_duplicates(text: str, bug_id: Optional[int] = None) -> Tuple[List[Dict], int]:
    """
    Find duplicate/similar bug reports.

    Args:
        text: Bug report description.
        bug_id: Optional current bug ID to exclude from results.

    Returns:
        (list_of_matches, count)
        Each match: {"bug_id": int, "similarity_score": float, "title": str}
    """
    return [], 0

