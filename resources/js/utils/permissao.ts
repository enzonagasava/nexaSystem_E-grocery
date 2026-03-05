/**
 * Verifica se o usuário pode ver um recurso segundo as permissões do painel ativo.
 * @param permissao - Nome no formato "recurso.acao" (ex.: chat.visualizar). Se undefined ou vazio, retorna true.
 * @param auth - Objeto auth de page.props (pode ser undefined se não autenticado).
 * @returns true se não exige permissão, se canAll, ou se permissoes inclui a permissão.
 */
export function podeVer(
  permissao: string | undefined,
  auth?: { canAll?: boolean; permissoes?: string[] } | null
): boolean {
  if (permissao === undefined || permissao === '') {
    return true
  }
  if (!auth) {
    return false
  }
  if (auth.canAll === true) {
    return true
  }
  return (auth.permissoes ?? []).includes(permissao)
}
