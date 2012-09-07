function Node(id, pid, name, url, title, target, icon, iconOpen, open) {
	this.id = id; // 节点id
	this.pid = pid; // 节点父id
	this.name = name; // 节点显示名称;
	this.url = url; // 节点超链接地址;
	this.title = title; // 节点Tips文本
	this.target = target; // 节点链接所打开的目标frame(_blank, _parent, _self, _top)
	this.icon = icon; // 节点默认图标;
	this.iconOpen = iconOpen; // 节点展开图标;
	this.open = open;
	this._io = open || false; // 节点展开标识;
	this._is = false; // 节点选中标识;
	this._ls = false; // 同级最后节点标识;
	this._hc = false; // 包含子节点标识;
	this._ai = 0; // 节点在节点数组中的索引值，初始值为0
	this._p; // 保存父节点对象;
};

function dTree(objName, htmlContainer) {
	this.config = {
		target : null,
		// 默认的节点链接所打开的目标frame(_blank, _parent, _self, _top)
		useSelection : true,
		// true高亮显示选中的节点;false反之;
		useCookies : true,
		// true使用Cookies保存节点状态;false反之;
		useIcons : true,
		// true使用图标;false反之;
		useStatusText : false,
		// false不在状态栏显示节点名称;true反之;
		closeSameLevel : false,
		// true同一级节点只能有一个处于展开状态;false反之;
		inOrder : false
	// false在整个节点数组中查找子节点;true在索引大于本节点的数组元素中查找子节点(如果子节点总是在父节点后面添加的话，设为true将加快tree的构建速度);
	};
	this.icon = {
		root : '/images/root.gif',
		// 根节点图标
		folder : '/images/folder.gif',
		// 枝节点文件夹图标
		folderOpen : '/images/folder_open.gif',
		// 枝节点打开状态文件夹图标
		node : '/images/page.gif',
		// 叶节点图标
		empty : '/images/empty.gif' // 空白图标
	};
	this.obj = objName; // 树对象名称(必须一致)
	this.aNodes = []; // 节点数组
	this.aNodesData = [];
	this.container = htmlContainer || 'dtree'; // 树所在的容器
	this.aIndent = []; // 当前节点到根节点次级节点(pid==-1)，所有父节点是否是同级节点中的最后一个，如果_ls==true则数组对应元素之为0，反之为1
	this.root = new Node(-1); // 默认根节点
	this.selectedNode = null; // 选中节点的id(tree初始化之前)或它在字节数组中的索引值_ai(tree初始化之后)
	this.selectedFound = false; // true存在选中的节点;false反之
	this.completed = false; // tree html 文本构造完成
};

dTree.prototype.add = function(id, pid, name, url, title, target, icon,
		iconOpen, open) {
	this.aNodesData[this.aNodesData.length] = new Node(id, pid, name, url,
			title, target, icon, iconOpen, open);
};

dTree.prototype.openAll = function() {
	this.oAll(true);
};

dTree.prototype.closeAll = function() {
	this.oAll(false);
};

dTree.prototype.addNode = function(pNode) {
	var str = '';
	var n = 0;
	if (this.config.inOrder)
		n = pNode._ai;
	for (n; n < this.aNodes.length; n++) {
		if (this.aNodes[n].pid == pNode.id) {
			var cn = this.aNodes[n];
			cn._p = pNode;
			cn._ai = n;
			this.setCS(cn);
			if (this.config.useSelection && cn.id == this.selectedNode
					&& !this.selectedFound) {
				cn._is = true;
				this.selectedNode = n;
				this.selectedFound = true;
			}
			str += this.node(cn, n);
			if (cn._ls)
				break;
		}
	}
	return str;
};

dTree.prototype.node = function(node, nodeId) {
	var str = '<h2>';
	str += '<a id="s' + this.obj + nodeId + '"';
	if (node.title) {
		str += ' title="' + node.title + '"';
	}
	if (node.target) {
		str += ' target="' + node.target + '"';
	}
	if (this.config.useStatusText)
		str += ' onmouseover="window.status=\''
				+ node.name
				+ '\';return true;" onmouseout="window.status=\'\';return true;" ';
	if (this.config.useSelection && node._is) {
		str += ' class="here"';
	}
	if (node.url) {
		str += ' href="' + node.url + '"';
	} else {
		str += ' href="javascript:void(0);"';
	}
	str += ' onclick="javascript: ' + this.obj + '.s(' + nodeId + ');';
	if (node._hc && node.pid != this.root.id) {
		str += this.obj + '.o(' + nodeId + ');';
	}
	str += '">';
	str += this.indent(node, nodeId);
	if (this.config.useIcons) {
		if (!node.icon)
			node.icon = (this.root.id == node.pid) ? this.icon.root
					: ((node._hc) ? this.icon.folder : this.icon.node);
		if (!node.iconOpen)
			node.iconOpen = (node._hc) ? this.icon.folderOpen : this.icon.node;
		str += '<img id="i' + this.obj + nodeId + '" class="node" src="'
				+ ((node._io) ? node.iconOpen : node.icon) + '" alt="'
				+ node.title + '" />';
	}
	str += '<span>' + node.name + '</span></a></h2>';
	if (node._hc) {
		str += '<div id="d' + this.obj + nodeId
				+ '" class="clip" style="display:'
				+ ((this.root.id == node.pid || node._io) ? '' : 'none')
				+ ';">';
		str += this.addNode(node);
		str += '</div>';
	}
	this.aIndent.pop();
	return str;
};

dTree.prototype.indent = function(node, nodeId) {
	var str = '';
	if (this.root.id != node.pid) {
		for ( var n = 0; n < this.aIndent.length; n++) {
			str += '<img src="' + this.icon.empty + '" alt="' + node.title
					+ '" />';
		}
		(node._ls) ? this.aIndent.push(0) : this.aIndent.push(1);
	}
	return str;
};

dTree.prototype.setCS = function(node) {
	var lastId;
	for ( var n = 0; n < this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id)
			node._hc = true;
		if (this.aNodes[n].pid == node.pid)
			lastId = this.aNodes[n].id;
	}
	if (lastId == node.id)
		node._ls = true;
};

dTree.prototype.delegate = function(id) {
};

dTree.prototype.draw = function() {
	this.aNodes = new Array();
	this.aIndent = new Array();
	for ( var i = 0; i < this.aNodesData.length; i++) {
		var oneNode = this.aNodesData[i];
		this.aNodes[i] = new Node(oneNode.id, oneNode.pid, oneNode.name,
				oneNode.url, oneNode.title, oneNode.target, oneNode.icon,
				oneNode.iconOpen, oneNode.open);
	}
	this.rewriteHTML();
};

dTree.prototype.rewriteHTML = function() {
	var str = '';
	var container;
	container = document.getElementById(this.container);
	if (!container) {
		alert('dTree can\'t find your specified container to show your tree.\n\n Please check your code!');
		return;
	}
	str += this.addNode(this.root);
	if (!this.selectedFound)
		this.selectedNode = null;
	this.completed = true;
	container.innerHTML = str;
};

dTree.prototype.s = function(id) {
	this.delegate(id);
	if (!this.config.useSelection)
		return;
	var cn = this.aNodes[id];
	if (cn._hc)
		return;
	if (this.selectedNode != id) {
		if (this.selectedNode || this.selectedNode == 0) {
			eOld = document.getElementById("s" + this.obj + this.selectedNode);
			eOld.className = "";
		}
		eNew = document.getElementById("s" + this.obj + id);
		eNew.className = "here";
		this.selectedNode = id;
	}
};

dTree.prototype.o = function(id) {
	var cn = this.aNodes[id];
	this.nodeStatus(!cn._io, id, cn._ls);
	cn._io = !cn._io;
	if (this.config.closeSameLevel)
		this.closeLevel(cn);
	this.delegate(id);
};

dTree.prototype.delayOpen = function(node) {
	var cn = node;
	var id = node._ai;
	if (cn._io == false) {
		var childrenDIV = document.getElementById('d' + this.obj + id);
		if (childrenDIV != null && childrenDIV.innerHTML == "") {
			var nodeTemp = cn;
			var indentArray = new Array();
			while (nodeTemp._p.id != this.root.id) {
				indentArray[indentArray.length] = (nodeTemp._ls) ? 0 : 1;
				nodeTemp = nodeTemp._p;
			}
			for ( var i = indentArray.length - 1; i >= 0; i--) {
				this.aIndent.push(indentArray[i]);
			}
			childrenDIV.innerHTML = this.addNode(cn);
			for ( var i = 0; i < indentArray.length; i++) {
				this.aIndent.pop();
			}
		}
	}
};

dTree.prototype.hasChildren = function(id) {
	for ( var i = 0; i < this.aNodesData.length; i++) {
		var oneNode = this.aNodesData[i];
		if (oneNode.pid == id)
			return true;
	}
	return false;
};

Array.prototype.remove = function(dx) {
	if (isNaN(dx) || dx > this.length) {
		return false;
	}
	for ( var i = 0, n = 0; i < this.length; i++) {
		if (this[i] != this[dx]) {
			this[n++] = this[i];
		}
	}
	this.length -= 1;
};

dTree.prototype.remove = function(id) {
	if (!this.hasChildren(id)) {
		for ( var i = 0; i < this.aNodesData.length; i++) {
			if (this.aNodesData[i].id == id) {
				this.aNodesData.remove(i);
			}
		}
	} else {
		alert("hasChildren!");
	}
};

dTree.prototype.oAll = function(status) {
	for ( var n = 0; n < this.aNodes.length; n++) {
		if (this.aNodes[n]._hc && this.aNodes[n].pid != this.root.id) {
			this.nodeStatus(status, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = status;
		}
	}
};

dTree.prototype.openTo = function(nId, bSelect, bFirst) {
	if (!bFirst) {
		for ( var n = 0; n < this.aNodes.length; n++) {
			if (this.aNodes[n].id == nId) {
				nId = n;
				break;
			}
		}
	}
	var cn = this.aNodes[nId];
	if (cn.pid == this.root.id || !cn._p)
		return;
	cn._io = true;
	cn._is = bSelect;
	if (this.completed && cn._hc)
		this.nodeStatus(true, cn._ai, cn._ls);
	if (this.completed && bSelect)
		this.s(cn._ai);
	else if (bSelect)
		this._sn = cn._ai;
	this.openTo(cn._p._ai, false, true);
};

dTree.prototype.closeLevel = function(node) {
	for ( var n = 0; n < this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.pid && this.aNodes[n].id != node.id
				&& this.aNodes[n]._hc) {
			this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);
		}
	}
};

dTree.prototype.closeAllChildren = function(node) {
	for ( var n = 0; n < this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id && this.aNodes[n]._hc) {
			if (this.aNodes[n]._io)
				this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);
		}
	}
};

dTree.prototype.nodeStatus = function(status, id, bottom) {
	eDiv = document.getElementById('d' + this.obj + id);
	if (this.config.useIcons) {
		eIcon = document.getElementById('i' + this.obj + id);
		eIcon.src = (status) ? this.aNodes[id].iconOpen : this.aNodes[id].icon;
	}
	eDiv.style.display = (status) ? 'block' : 'none';
};

if (!Array.prototype.push) {
	Array.prototype.push = function array_push() {
		for ( var i = 0; i < arguments.length; i++)
			this[this.length] = arguments[i];
		return this.length;
	};
};
if (!Array.prototype.pop) {
	Array.prototype.pop = function array_pop() {
		lastElement = this[this.length - 1];
		this.length = Math.max(this.length - 1, 0);
		return lastElement;
	};
};

