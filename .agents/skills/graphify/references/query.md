# Graphify Query & Traversal Reference

This document provides query patterns and traversal examples for navigating the `graphify-out/graph.json` knowledge graph.

---

## 1. Query Modes

### BFS (Breadth-First Search) - Default
- **Purpose**: Broad architectural exploration. Identifies nearest neighbours and immediate dependencies first.
- **Best For**:
  - "What does `UserController` connect to?"
  - "Which tables and models interact with `GpxActivity`?"
  - "Show all direct callers of `CalculatePace`."

### DFS (Depth-First Search)
- **Purpose**: Deep tracing along a dependency chain.
- **Best For**:
  - "How does a route request reach the database query?"
  - "What is the full lifecycle chain of event handling?"
  - "Trace data flow from HTTP request down to external storage."

---

## 2. Querying with the CLI

```bash
# General query with BFS traversal
graphify query "how does user authentication work?"

# DFS deep trace capped at 1500 tokens
graphify query "trace athlete training plan enrollment" --dfs --budget 1500

# Shortest path between two symbols
graphify path "GpxController" "Activity"

# Detailed explanation of a single node
graphify explain "PaceCalculator"
```

---

## 3. Querying with NetworkX in Python Scripts

When automating within an agent session, you can run quick inline queries:

```python
import json
from pathlib import Path
import networkx as nx
from networkx.readwrite import json_graph

graph_file = Path("graphify-out/graph.json")
if not graph_file.exists():
    raise FileNotFoundError("graphify-out/graph.json not found. Run 'graphify .' first.")

data = json.loads(graph_file.read_text(encoding="utf-8"))
G = json_graph.node_link_graph(data, edges="links")

def search_nodes(query_str):
    q = query_str.lower()
    return [
        (n, d) for n, d in G.nodes(data=True)
        if q in d.get("label", "").lower() or q in d.get("source_file", "").lower()
    ]

# Example: find all models
models = [d.get("label") for n, d in G.nodes(data=True) if "app/models" in d.get("source_file", "").lower()]
print(f"Found {len(models)} models: {', '.join(models[:10])}")
```
