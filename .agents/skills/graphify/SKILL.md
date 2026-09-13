---
name: graphify
description: Turn this codebase into a local deterministic knowledge graph (nodes, relationships, communities, AST edges). Use to quickly map architecture, query dependencies, and accelerate prompt execution with 70x fewer tokens.
trigger: /graphify
---

# Graphify: Local Codebase Knowledge Graph & Brain

Graphify analyzes your project files (PHP, Blade, JS, TS, Python, SQL, Markdown, docs) and generates a structured local knowledge graph with AST dependency parsing, community clustering, and cross-document relationship mapping.

Outputs are stored entirely locally in `graphify-out/`:
- `graphify-out/graph.json`: Persistent machine-readable graph for fast query traversal.
- `graphify-out/graph.html`: Interactive 2D/3D force-directed visualizer (open directly in any browser, zero server needed).
- `graphify-out/GRAPH_REPORT.md`: Comprehensive audit report covering god nodes, tight couplings, isolated modules, and suggested questions.
- `graphify-out/obsidian/`: Visual Obsidian vault with canvas layout.

---

## Installation & Requirements

Graphify requires Python 3.10+ on your system.

### Option 1: Install via pip / pipx (Recommended)
```bash
pip install graphifyy
```
*(Note: Package on PyPI is `graphifyy`, command line binary is `graphify`)*

On Windows, if `graphify` is not in your PATH, you can run it via Python directly:
```bash
python -m graphify .
```
or install with pipx:
```bash
pipx install graphifyy
```

### Option 2: Install via uv
```bash
uv tool install graphifyy
```

---

## Usage Commands

### 1. Build Full Graph
```bash
# Run on current workspace
graphify .

# Or run via python module
python -m graphify .
```

Options:
- `graphify . --mode deep`: Deep semantic extraction with richer inferred connections.
- `graphify . --no-viz`: Build JSON and markdown report only (skip visualizers).
- `graphify . --svg`: Export standalone `graph.svg` for embedding.
- `graphify . --watch`: Watch project for file changes and auto-update AST graph.

### 2. Incremental Update (Fast)
When you add or edit code files, update only changed files instead of re-indexing the whole repo:
```bash
graphify . --update
```

### 3. Query the Graph (Instant Concept Traversal)
Ask specific architectural questions without reading raw files:
```bash
graphify query "how does GPX parsing connect to workout sessions?"
graphify query "what models depend on User" --dfs
graphify path "GpxController" "Program"
graphify explain "PaceCalculator"
```

---

## Execution Workflow for Agents

When building or querying the graph autonomously in this repository, follow this deterministic sequence:

### Step 1: Detect Files
Inspect repo composition and word count:
```bash
python -c "
import json
from pathlib import Path
from graphify.detect import detect
res = detect(Path('.'))
print(f'Code files: {len(res.get(\"files\", {}).get(\"code\", []))}')
print(f'Doc files:  {len(res.get(\"files\", {}).get(\"document\", []))}')
print(f'Total words: {res.get(\"total_words\", 0)}')
"
```

### Step 2: Extract AST & Relations
Run structural code extraction:
```bash
python -c "
import json
from pathlib import Path
from graphify.extract import collect_files, extract
from graphify.detect import detect

detection = detect(Path('.'))
code_files = []
for f in detection.get('files', {}).get('code', []):
    p = Path(f)
    code_files.extend(collect_files(p) if p.is_dir() else [p])

if code_files:
    res = extract(code_files)
    Path('.graphify_extract.json').write_text(json.dumps(res, indent=2))
    print(f'Extracted {len(res.get(\"nodes\", []))} nodes, {len(res.get(\"edges\", []))} edges')
"
```

### Step 3: Build, Cluster, and Generate Outputs
```bash
python -c "
import json
from pathlib import Path
from graphify.build import build_from_json
from graphify.cluster import cluster, score_all
from graphify.analyze import god_nodes, surprising_connections, suggest_questions
from graphify.report import generate
from graphify.export import to_json, to_html, to_obsidian

Path('graphify-out').mkdir(parents=True, exist_ok=True)
ext = json.loads(Path('.graphify_extract.json').read_text())
detection = json.loads(Path('.graphify_detect.json').read_text()) if Path('.graphify_detect.json').exists() else {'total_files': len(ext['nodes']), 'total_words': 10000, 'files': {}}

G = build_from_json(ext)
comm = cluster(G)
cohesion = score_all(G, comm)
gods = god_nodes(G)
surprises = surprising_connections(G, comm)
labels = {cid: f'Community {cid}' for cid in comm}
questions = suggest_questions(G, comm, labels)

report = generate(G, comm, cohesion, labels, gods, surprises, detection, {'input': 0, 'output': 0}, '.', suggested_questions=questions)
Path('graphify-out/GRAPH_REPORT.md').write_text(report, encoding='utf-8')
to_json(G, comm, 'graphify-out/graph.json')
to_html(G, comm, 'graphify-out/graph.html', community_labels=labels)
to_obsidian(G, comm, 'graphify-out/obsidian', community_labels=labels, cohesion=cohesion)

print(f'Done! Graph has {G.number_of_nodes()} nodes, {G.number_of_edges()} edges, {len(comm)} communities')
"
```

---

## Agent Fast-Lookup from `graphify-out/graph.json`

Once `graphify-out/graph.json` exists, you can locate symbols, callers, and dependencies with zero token overhead using standard Python:

```python
import json
from pathlib import Path
import networkx as nx
from networkx.readwrite import json_graph

data = json.loads(Path("graphify-out/graph.json").read_text(encoding="utf-8"))
G = json_graph.node_link_graph(data, edges="links")

# Find node by label
target = "GpxController"
matches = [n for n, d in G.nodes(data=True) if target.lower() in d.get("label", "").lower()]

for m in matches:
    print(f"Node: {G.nodes[m].get('label')} ({G.nodes[m].get('source_file')})")
    for nbr in G.neighbors(m):
        edge = G.edges[m, nbr]
        print(f"  --> {edge.get('relation')} --> {G.nodes[nbr].get('label')}")
```

---

## Guidelines & Rules

1. **Local-First & Zero Leakage**: All extraction runs locally on your machine. No source code or proprietary schemas are uploaded to any external server.
2. **Deterministic Confidence**: Nodes and edges are tagged with explicit confidence levels:
   - `EXTRACTED`: Explicit AST import, class inheritance, method invocation, route target.
   - `INFERRED`: Derived association or shared schema state.
   - `AMBIGUOUS`: Unresolved reference flagged for manual review.
3. **Keep Graph Fresh**: When working on large features or adding multiple new files, run `graphify . --update` so the graph remains aligned with current code.
