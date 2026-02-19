#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
CLI para gerenciar tarefas no Jira via API REST (Jira Cloud).
Uso: variáveis JIRA_SITE, JIRA_EMAIL e JIRA_API_TOKEN no .env ou no ambiente.

Comandos disponíveis:
  create      - Criar uma nova issue/tarefa
  get         - Buscar informações de uma issue
  list        - Listar todas as issues de um projeto (ex.: EBC = ExoBookingCore)
  transitions - Listar transições disponíveis para uma issue
  transition  - Executar uma transição de status em uma issue
  update      - Atualizar uma issue existente
"""

import argparse
import base64
import json
import os
import sys
from pathlib import Path
from typing import Optional

# Garantir encoding UTF-8 para stdin/stdout/stderr
if sys.platform == 'win32':
    import io
    sys.stdin = io.TextIOWrapper(sys.stdin.buffer, encoding='utf-8')
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

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


def fix_encoding(text: str) -> str:
    """
    Corrige problemas de encoding (ex.: UTF-8 interpretado como Latin-1, mojibake).
    Usado tanto na CLI quanto ao normalizar textos vindos da API do Jira.
    """
    if not text:
        return text

    # Tenta corrigir mojibake: bytes UTF-8 lidos como Latin-1 (comum em APIs)
    try:
        corrected = text.encode("latin-1").decode("utf-8")
        if corrected != text:
            return corrected
    except (UnicodeEncodeError, UnicodeDecodeError):
        pass

    # Garante que está em UTF-8 válido
    try:
        text.encode("utf-8").decode("utf-8")
        return text
    except (UnicodeEncodeError, UnicodeDecodeError):
        pass

    try:
        return text.encode("latin-1").decode("utf-8")
    except (UnicodeEncodeError, UnicodeDecodeError):
        pass

    return text


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
        "Content-Type": "application/json; charset=utf-8",
        "Authorization": get_auth_header(),
    }
    # Garantir texto em UTF-8 (normalizar para evitar erros de codificação)
    summary_utf8 = summary.encode("utf-8").decode("utf-8") if summary else ""
    desc_utf8 = description.strip().encode("utf-8").decode("utf-8") if description else ""
    # Descrição em formato Atlassian Document (ADF) para API v3
    content = []
    if desc_utf8:
        for line in desc_utf8.splitlines():
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
        "summary": summary_utf8,
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
    body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    r = requests.post(url, headers=headers, data=body, timeout=30)
    r.raise_for_status()
    return r.json()


def update_issue(
    issue_key: str,
    summary: Optional[str] = None,
    description: Optional[str] = None,
) -> dict:
    """Atualiza uma issue no Jira (REST API v3)."""
    url = f"https://{JIRA_SITE}/rest/api/3/issue/{issue_key}"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json; charset=utf-8",
        "Authorization": get_auth_header(),
    }
    
    fields = {}
    if summary is not None:
        fields["summary"] = summary.encode("utf-8").decode("utf-8")
    if description is not None:
        desc_utf8 = description.strip().encode("utf-8").decode("utf-8") if description else ""
        # Descrição em formato Atlassian Document (ADF) para API v3
        content = []
        if desc_utf8:
            for line in desc_utf8.splitlines():
                content.append({"type": "paragraph", "content": [{"type": "text", "text": line}]})
        if not content:
            content = [{"type": "paragraph", "content": [{"type": "text", "text": ""}]}]
        fields["description"] = {"type": "doc", "version": 1, "content": content}
    
    if not fields:
        raise ValueError("Pelo menos um campo (summary ou description) deve ser fornecido")
    
    payload = {"fields": fields}
    body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    r = requests.put(url, headers=headers, data=body, timeout=30)
    r.raise_for_status()
    # PUT pode retornar 204 No Content (sem corpo)
    try:
        return r.json() if r.content else {}
    except ValueError:
        return {}


def get_issue(issue_key: str) -> dict:
    """Busca uma issue do Jira."""
    url = f"https://{JIRA_SITE}/rest/api/3/issue/{issue_key}"
    headers = {
        "Accept": "application/json",
        "Authorization": get_auth_header(),
    }
    r = requests.get(url, headers=headers, timeout=30)
    r.raise_for_status()
    return r.json()


def search_issues(project_key: str, max_results: int = 100, fields: Optional[list] = None) -> list:
    """
    Lista issues do projeto via JQL (API REST v3 /rest/api/3/search/jql).
    Retorna lista de issues com key e fields (summary, description por padrão).
    Usa nextPageToken para paginação (API atual do Jira Cloud).
    """
    url = f"https://{JIRA_SITE}/rest/api/3/search/jql"
    headers = {
        "Accept": "application/json",
        "Authorization": get_auth_header(),
    }
    if fields is None:
        fields = ["summary", "description"]
    jql = f"project = {project_key}"
    all_issues = []
    next_page_token = None
    page_size = min(50, max_results)
    while len(all_issues) < max_results:
        params = {
            "jql": jql,
            "maxResults": page_size,
            "fields": ",".join(fields),
        }
        if next_page_token is not None:
            params["nextPageToken"] = next_page_token
        r = requests.get(url, headers=headers, params=params, timeout=30)
        r.raise_for_status()
        data = r.json()
        issues = data.get("issues", [])
        if not issues:
            break
        all_issues.extend(issues)
        next_page_token = data.get("nextPageToken")
        if not next_page_token or len(all_issues) >= max_results:
            break
    return all_issues[:max_results]


def get_transitions(issue_key: str) -> list:
    """Obtém as transições disponíveis para uma issue."""
    url = f"https://{JIRA_SITE}/rest/api/3/issue/{issue_key}/transitions"
    headers = {
        "Accept": "application/json",
        "Authorization": get_auth_header(),
    }
    r = requests.get(url, headers=headers, timeout=30)
    r.raise_for_status()
    return r.json().get("transitions", [])


def transition_issue(issue_key: str, transition_id: str) -> dict:
    """Executa uma transição de status em uma issue."""
    url = f"https://{JIRA_SITE}/rest/api/3/issue/{issue_key}/transitions"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "Authorization": get_auth_header(),
    }
    payload = {
        "transition": {"id": transition_id}
    }
    r = requests.post(url, headers=headers, json=payload, timeout=30)
    r.raise_for_status()
    # Transição pode retornar 204 No Content (corpo vazio)
    try:
        return r.json() if r.content else {}
    except ValueError:
        return {}


def format_description(desc) -> str:
    """Formata a descrição de uma issue (ADF ou texto simples)."""
    if isinstance(desc, dict) and 'content' in desc:
        lines = []
        for item in desc.get('content', []):
            if item.get('type') == 'paragraph':
                for content in item.get('content', []):
                    if content.get('type') == 'text':
                        lines.append(content.get('text', ''))
        return '\n'.join(lines)
    return desc if desc else "(sem descrição)"


def main():
    parser = argparse.ArgumentParser(description="Gerenciar tarefas no Jira via CLI")
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

    # get
    get_parser = subparsers.add_parser("get", help="Buscar informações de uma issue")
    get_parser.add_argument("issue_key", help="Chave da issue (ex: EBC-1)")
    get_parser.add_argument("--json", action="store_true", help="Exibir resultado completo em JSON")

    # transitions
    transitions_parser = subparsers.add_parser("transitions", help="Listar transições disponíveis para uma issue")
    transitions_parser.add_argument("issue_key", help="Chave da issue (ex: EBC-1)")

    # transition
    transition_parser = subparsers.add_parser("transition", help="Executar uma transição de status em uma issue")
    transition_parser.add_argument("issue_key", help="Chave da issue (ex: EBC-1)")
    transition_parser.add_argument("transition_id", help="ID da transição (use 'transitions' para listar)")

    # update
    update_parser = subparsers.add_parser("update", help="Atualizar uma issue existente")
    update_parser.add_argument("issue_key", help="Chave da issue (ex: EBC-1)")
    update_parser.add_argument("--summary", "-s", default="", help="Novo título da issue")
    update_parser.add_argument("--description", "-d", default="", help="Nova descrição da issue")
    update_parser.add_argument("--description-file", default="", help="Ler descrição de um arquivo (sobrescreve -d)")

    # list – listar issues do projeto (quadro ExoBookingCore = projeto EBC)
    list_parser = subparsers.add_parser("list", help="Listar todas as issues do projeto (ex.: EBC)")
    list_parser.add_argument("--project", "-p", default="EBC", help="Chave do projeto (padrão: EBC)")
    list_parser.add_argument("--max-results", "-n", type=int, default=100, help="Máximo de issues (padrão: 100)")

    args = parser.parse_args()

    if not JIRA_SITE or not get_auth_header():
        print("Erro: defina JIRA_SITE, JIRA_EMAIL e JIRA_API_TOKEN no .env ou no ambiente.", file=sys.stderr)
        print("Copie .env.example para .env e preencha os valores.", file=sys.stderr)
        sys.exit(1)

    try:
        if args.command == "create":
            # Garantir encoding UTF-8 para argumentos da linha de comando
            summary = fix_encoding(args.summary) if args.summary else ""
            description = fix_encoding(args.description) if args.description else ""
            
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
            data = create_issue(
                project_key=args.project,
                summary=summary,
                description=description,
                issue_type=args.type,
                assignee_display_name=assignee,
                start_date=start_date,
            )
            key = data.get("key", "?")
            print(f"Tarefa criada: {key}")
            print(f"URL: https://{JIRA_SITE}/browse/{key}")

        elif args.command == "get":
            issue = get_issue(args.issue_key)
            if args.json:
                print(json.dumps(issue, indent=2, ensure_ascii=False))
            else:
                print(f"\n=== Issue: {args.issue_key} ===")
                print(f"Título: {issue['fields']['summary']}")
                print(f"Status: {issue['fields']['status']['name']}")
                print(f"\nDescrição:")
                desc = issue['fields'].get('description', {})
                print(format_description(desc))

        elif args.command == "transitions":
            transitions = get_transitions(args.issue_key)
            print(f"\n=== Transições disponíveis para {args.issue_key} ===")
            if transitions:
                for trans in transitions:
                    to_status = trans.get('to', {}).get('name', 'N/A')
                    print(f"  ID: {trans['id']} - Nome: {trans['name']} - Para: {to_status}")
            else:
                print("  Nenhuma transição disponível.")

        elif args.command == "transition":
            transition_issue(args.issue_key, args.transition_id)
            print(f"Transição executada com sucesso para {args.issue_key}")
            # Mostra o novo status
            issue = get_issue(args.issue_key)
            print(f"Novo status: {issue['fields']['status']['name']}")

        elif args.command == "update":
            summary = None
            description = None
            
            if args.summary:
                summary = fix_encoding(args.summary)
            
            if args.description_file:
                p = Path(args.description_file)
                if not p.is_absolute():
                    p = ROOT / p
                if p.exists():
                    description = p.read_text(encoding="utf-8")
                else:
                    print(f"Aviso: arquivo não encontrado: {args.description_file}", file=sys.stderr)
            elif args.description:
                description = fix_encoding(args.description)
            
            update_issue(args.issue_key, summary=summary, description=description)
            print(f"Issue atualizada: {args.issue_key}")
            print(f"URL: https://{JIRA_SITE}/browse/{args.issue_key}")

        elif args.command == "list":
            issues = search_issues(args.project, max_results=args.max_results)
            print(f"\n=== Issues do projeto {args.project} ({len(issues)} encontradas) ===\n")
            for issue in issues:
                key = issue.get("key", "?")
                summary = (issue.get("fields") or {}).get("summary", "")
                print(f"  {key}: {summary}")
            if not issues:
                print("  Nenhuma issue encontrada.")

        else:
            parser.print_help()
            sys.exit(0)

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


if __name__ == "__main__":
    main()
