#!/usr/bin/env python3
"""
CLI para criar tarefas no Jira via API REST (Jira Cloud).
Uso: variáveis JIRA_SITE, JIRA_EMAIL e JIRA_API_TOKEN no .env ou no ambiente.
"""

import argparse
import base64
import json
import os
import sys
from datetime import date
from pathlib import Path
from typing import Optional

try:
    import requests
    from dotenv import load_dotenv
except ImportError:
    print("Instale as dependências: pip install -r requirements-jira.txt", file=sys.stderr)
    sys.exit(1)

# Carrega .env na raiz do projeto
ROOT = Path(__file__).resolve().parent.parent
load_dotenv(ROOT / ".env")

JIRA_SITE = os.getenv("JIRA_SITE", "").strip().rstrip("/")
JIRA_EMAIL = os.getenv("JIRA_EMAIL", "").strip()
JIRA_API_TOKEN = os.getenv("JIRA_API_TOKEN", "").strip()


def get_auth_header():
    """Basic auth: email:api_token em Base64 (Jira Cloud)."""
    if not JIRA_EMAIL or not JIRA_API_TOKEN:
        return None
    raw = f"{JIRA_EMAIL}:{JIRA_API_TOKEN}"
    b64 = base64.b64encode(raw.encode()).decode()
    return f"Basic {b64}"


def resolve_assignee(project_key: str, display_name_query: str) -> str | None:
    """Retorna accountId do primeiro usuário atribuível cujo displayName contém a query."""
    url = f"https://{JIRA_SITE}/rest/api/3/user/assignable/search"
    headers = {"Accept": "application/json", "Authorization": get_auth_header()}
    params = {"query": display_name_query.strip(), "project": project_key}
    r = requests.get(url, headers=headers, params=params, timeout=15)
    r.raise_for_status()
    users = r.json() if isinstance(r.json(), list) else []
    query_lower = display_name_query.strip().lower()
    for u in users:
        if query_lower in (u.get("displayName") or "").lower():
            return u.get("accountId")
    # Fallback: primeiro resultado se a query for parte do nome
    for u in users:
        if u.get("accountId"):
            return u.get("accountId")
    return None


def create_issue(
    project_key: str,
    summary: str,
    description: str = "",
    issue_type: str = "Task",
    assignee_display_name: Optional[str] = None,
    start_date: Optional[str] = None,
) -> dict:
    """Cria uma issue no Jira (REST API v3). start_date: YYYY-MM-DD."""
    url = f"https://{JIRA_SITE}/rest/api/3/issue"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "Authorization": get_auth_header(),
    }
    # Descrição em formato Atlassian Document (ADF) para API v3
    content = []
    if description:
        for line in description.strip().splitlines():
            content.append({"type": "paragraph", "content": [{"type": "text", "text": line}]})
    if not content:
        content = [{"type": "paragraph", "content": [{"type": "text", "text": ""}]}]

    # Tipo por ID (numérico) ou por nome
    if issue_type.isdigit():
        issuetype_ref = {"id": issue_type}
    else:
        issuetype_ref = {"name": issue_type}

    fields = {
        "project": {"key": project_key},
        "summary": summary,
        "issuetype": issuetype_ref,
        "description": {"type": "doc", "version": 1, "content": content},
    }
    if assignee_display_name:
        account_id = resolve_assignee(project_key, assignee_display_name)
        if account_id:
            fields["assignee"] = {"accountId": account_id}
    if start_date:
        fields["startDate"] = start_date  # Pode ser ignorado se o projeto não tiver o campo

    payload = {"fields": fields}
    r = requests.post(url, headers=headers, json=payload, timeout=30)
    r.raise_for_status()
    return r.json()


def main():
    parser = argparse.ArgumentParser(description="Criar tarefas no Jira via CLI")
    subparsers = parser.add_subparsers(dest="command", help="Comando")

    # create
    create_parser = subparsers.add_parser("create", help="Criar uma nova issue/tarefa")
    create_parser.add_argument("--project", "-p", required=True, help="Chave do projeto (ex: PROJ)")
    create_parser.add_argument("--summary", "-s", required=True, help="Título da tarefa")
    create_parser.add_argument("--description", "-d", default="", help="Descrição da tarefa")
    create_parser.add_argument("--description-file", default="", help="Ler descrição de um arquivo (sobrescreve -d)")
    create_parser.add_argument("--type", "-t", default="Task", help="Tipo da issue (ex: Task, História, Bug)")
    create_parser.add_argument("--assignee", "-a", default="", help="Nome do responsável (ex: Gabriel Maciel)")
    create_parser.add_argument("--start-date", default="", help="Data de início YYYY-MM-DD (padrão: hoje)")

    args = parser.parse_args()

    if not JIRA_SITE or not get_auth_header():
        print("Erro: defina JIRA_SITE, JIRA_EMAIL e JIRA_API_TOKEN no .env ou no ambiente.", file=sys.stderr)
        print("Copie .env.example para .env e preencha os valores.", file=sys.stderr)
        sys.exit(1)

    if args.command == "create":
        description = args.description
        if args.description_file:
            p = Path(args.description_file)
            if not p.is_absolute():
                p = ROOT / p
            if p.exists():
                description = p.read_text(encoding="utf-8")
            else:
                print(f"Aviso: arquivo não encontrado: {args.description_file}", file=sys.stderr)
        start_date = args.start_date.strip() or None  # só envia se informado (projeto pode não ter o campo)
        assignee = args.assignee.strip() or None
        try:
            data = create_issue(
                project_key=args.project,
                summary=args.summary,
                description=description,
                issue_type=args.type,
                assignee_display_name=assignee,
                start_date=start_date,
            )
            key = data.get("key", "?")
            print(f"Tarefa criada: {key}")
            print(f"URL: https://{JIRA_SITE}/browse/{key}")
        except requests.HTTPError as e:
            body = e.response.text
            try:
                err = e.response.json()
                body = json.dumps(err, indent=2, ensure_ascii=False)
            except Exception:
                pass
            print(f"Erro Jira ({e.response.status_code}): {body}", file=sys.stderr)
            sys.exit(1)
        except requests.RequestException as e:
            print(f"Erro de rede: {e}", file=sys.stderr)
            sys.exit(1)
    else:
        parser.print_help()
        sys.exit(0)


if __name__ == "__main__":
    main()
