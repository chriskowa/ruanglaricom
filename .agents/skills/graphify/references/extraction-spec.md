# Graphify Knowledge Graph Schema & Extraction Spec

This specification defines the JSON schema for nodes, edges, and communities used by Graphify.

---

## 1. Node Schema

Each node in `graphify-out/graph.json` represents a discrete code entity (class, method, file, table, interface) or conceptual document entity:

```json
{
  "id": "app_models_user",
  "label": "User",
  "file_type": "code",
  "source_file": "app/Models/User.php",
  "source_location": "L12-L140",
  "source_url": null,
  "captured_at": "2026-09-13T10:00:00Z",
  "author": null,
  "contributor": null,
  "community": 2
}
```

### Supported `file_type` Values
- `code`: Executable source files (`.php`, `.js`, `.ts`, `.py`, `.sql`)
- `document`: Markdown documentation, notes, specifications (`.md`, `.txt`, `.rst`)
- `paper`: Academic papers, mathematical specifications (`.pdf`, converted papers)
- `image`: Architectural diagrams, flowcharts, screenshots (`.png`, `.jpg`, `.svg`)

---

## 2. Edge Schema

Edges represent relationships between two nodes:

```json
{
  "source": "app_http_controllers_gpxcontroller",
  "target": "app_models_activity",
  "relation": "calls",
  "confidence": "EXTRACTED",
  "source_file": "app/Http/Controllers/GpxController.php",
  "source_location": "L85",
  "weight": 1.0
}
```

### Supported Relations
- `calls`: Direct method invocation or function call
- `implements`: Interface implementation or trait usage
- `extends`: Class inheritance
- `references`: Explicit reference in documentation or comments
- `cites`: Citation between papers or external sources
- `conceptually_related_to`: Semantic link identified by community clustering
- `shares_data_with`: Shared database table, cache key, or state mutation

### Confidence Values
- `EXTRACTED`: Explicit syntax match found by deterministic AST parser. 100% confidence.
- `INFERRED`: Logical connection based on conventions (e.g. controller naming matching model or route mapping).
- `AMBIGUOUS`: Potential link that could not be deterministically verified. Kept for manual review.

---

## 3. Communities & Clustering

Graphify uses the Leiden / Louvain algorithm to partition the graph into modular clusters:
- **God Nodes**: High-degree central nodes that touch multiple subsystems (e.g. `User`, `DatabaseManager`).
- **Bridges**: Entities that connect two otherwise isolated communities (critical for refactoring impact analysis).
- **Cohesion Score**: Numerical measurement (0.0 - 1.0) of how tightly coupled the internal members of a community are.
